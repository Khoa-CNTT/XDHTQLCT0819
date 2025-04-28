<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\RequestException;

class TransactionController extends Controller
{
    public function fetchMBBankTransactions()
    {
        try {
            $client = new \GuzzleHttp\Client();

            $payload = [
                "USERNAME"  => "0983057130",
                "PASSWORD"  => "Letronghuy113+",
                "DAY_BEGIN" => Carbon::today()->format('d/m/Y'),
                "DAY_END"   => Carbon::today()->format('d/m/Y'),
                "NUMBER_MB" => "0983057130"
            ];

            Log::info('MB API Request Payload:', $payload);

       
            $response = $client->post('https://api-mb.dzmid.io.vn/api/transactions', [
                'json' => $payload, 
            ]);

            $data = json_decode($response->getBody(), true); 

      
            Log::info('MB API Response Data: ', $data['data']['transactionHistoryList']);

            return response()->json([
                'success' => true,
                'data' => $data['data']['transactionHistoryList'] 
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('MB API Request Exception: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Gửi yêu cầu tới MB Bank thất bại. Vui lòng thử lại sau.'
            ], 500);
        } catch (\Exception $e) {
            Log::error('MB API General Exception: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Có lỗi xảy ra. Vui lòng thử lại sau.'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/transactions",
     *     summary="Lấy danh sách giao dịch",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Ngày giao dịch",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="month",
     *         in="query",
     *         description="Tháng giao dịch",
     *         required=false,
     *         @OA\Schema(type="string", format="month", example="2025-01")
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="Năm giao dịch",
     *         required=false,
     *         @OA\Schema(type="integer", example=2025)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách giao dịch",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *         )
     *     ),
     *     @OA\Response(response=500, description="Lỗi hệ thống")
     * )
     */

    public function index(Request $request)
    {
        try {
            $userId = auth()->id();

            $query = Transaction::where('user_id', $userId);

            if ($request->has('date')) {
                $date = Carbon::parse($request->input('date'))->format('Y-m-d');
                $query->whereDate('transaction_date', $date);
            }

            if ($request->has('month')) {
                $month = Carbon::parse($request->input('month'))->format('Y-m');
                $query->whereMonth('transaction_date', '=', $month);
            }

            if ($request->has('year')) {
                $year = $request->input('year');
                $query->whereYear('transaction_date', '=', $year);
            }

            $transactions = $query->get();

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải giao dịch:: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Không thể lấy giao dịch tại thời điểm này. Vui lòng thử lại sau'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/transactions",
     *     summary="Tạo giao dịch mới",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="account_id", type="integer", example=1, description="ID của tài khoản (nullable)"),
     *             @OA\Property(property="category_id", type="integer", example=1, description="ID của danh mục (nullable)"),
     *             @OA\Property(property="amount", type="number", format="float", example=1000, description="Số tiền giao dịch"),
     *             @OA\Property(property="transaction_date", type="string", format="date", example="2025-01-01", description="Ngày giao dịch"),
     *             @OA\Property(property="type", type="string", enum={"cash", "transfer"}, example="cash", description="Loại giao dịch"),
     *             @OA\Property(property="address", type="string", example="Hà Nội", description="Địa chỉ giao dịch"),
     *             @OA\Property(property="recurring_transaction", type="boolean", example=true, description="Giao dịch định kỳ"),
     *             @OA\Property(property="description", type="string", example="Thanh toán hóa đơn", description="Mô tả giao dịch (nullable)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tạo giao dịch thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Thêm giao dịch thành công."),
     *         )
     *     ),
     *     @OA\Response(response=422, description="Dữ liệu không hợp lệ"),
     *     @OA\Response(response=500, description="Lỗi hệ thống")
     * )
     */

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'account_id'            => 'nullable|exists:accounts,id',
                'category_id'           => 'nullable|exists:categories,id',
                'amount'                => 'required|numeric|min:0',
                'transaction_date'      => 'required|date',
                'type'                  => 'required|in:cash,transfer',
                'address'               => 'required|string|max:255',
                'recurring_transaction' => 'required|boolean',
                'description'           => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ.',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            $data['user_id'] = auth()->id();

            $transaction = Transaction::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Thêm giao dịch thành công.',
                'data'    => $transaction
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải giao dịch:: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Không thể lấy giao dịch tại thời điểm này. Vui lòng thử lại sau'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/transactions/{id}",
     *     summary="Lấy thông tin giao dịch",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}}, 
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của giao dịch",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lấy thông tin giao dịch thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Lấy thông tin giao dịch thành công."),
     *         )
     *     ),
     *     @OA\Response(response=404, description="Không tìm thấy giao dịch"),
     *     @OA\Response(response=500, description="Lỗi hệ thống")
     * )
     */

    public function edit($id)
    {
        try {
            $transaction = Transaction::where('user_id', auth()->id())->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải giao dịch:: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Không thể lấy giao dịch tại thời điểm này. Vui lòng thử lại sau'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/transactions/{id}",
     *     summary="Cập nhật giao dịch",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của giao dịch",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="account_id", type="integer", example=1, description="ID của tài khoản (nullable)"),
     *             @OA\Property(property="category_id", type="integer", example=1, description="ID của danh mục (nullable)"),
     *             @OA\Property(property="amount", type="number", format="float", example=1500, description="Số tiền giao dịch"),
     *             @OA\Property(property="transaction_date", type="string", format="date", example="2025-01-15", description="Ngày giao dịch"),
     *             @OA\Property(property="type", type="string", enum={"cash", "transfer"}, example="transfer", description="Loại giao dịch"),
     *             @OA\Property(property="address", type="string", example="TP. Hồ Chí Minh", description="Địa chỉ giao dịch"),
     *             @OA\Property(property="recurring_transaction", type="boolean", example=false, description="Giao dịch định kỳ"),
     *             @OA\Property(property="description", type="string", example="Nộp tiền", description="Mô tả giao dịch (nullable)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật giao dịch thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Cập nhật giao dịch thành công."),
     *         )
     *     ),
     *     @OA\Response(response=404, description="Không tìm thấy giao dịch"),
     *     @OA\Response(response=422, description="Dữ liệu không hợp lệ"),
     *     @OA\Response(response=500, description="Lỗi hệ thống")
     * )
     */

    public function update(Request $request, $id)
    {
        try {
            $transaction = Transaction::where('user_id', auth()->id())->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'account_id'            => 'nullable|exists:accounts,id',
                'category_id'           => 'nullable|exists:categories,id',
                'amount'                => 'required|numeric|min:0',
                'transaction_date'      => 'required|date',
                'type'                  => 'required|in:cash,transfer',
                'address'               => 'required|string|max:255',
                'recurring_transaction' => 'required|boolean',
                'description'           => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ.',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            $transaction->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật giao dịch thành công.',
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải giao dịch:: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Không thể lấy giao dịch tại thời điểm này. Vui lòng thử lại sau'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/transactions/{id}",
     *     summary="Xóa giao dịch",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}}, 
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của giao dịch",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa giao dịch thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Xóa giao dịch thành công.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Không tìm thấy giao dịch"),
     *     @OA\Response(response=500, description="Lỗi hệ thống")
     * )
     */
    public function destroy($id)
    {
        try {
            $transaction = Transaction::where('user_id', auth()->id())->findOrFail($id);
            $transaction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa giao dịch thành công.'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải giao dịch:: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Không thể lấy giao dịch tại thời điểm này. Vui lòng thử lại sau'
            ], 500);
        }
    }
}
