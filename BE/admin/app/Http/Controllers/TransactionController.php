<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class TransactionController extends Controller
{

    public function fetchMBBankTransactions()
    {
        try {
            $account = Account::where('user_id', auth()->user()->id)
                ->where('is_primary', true)
                ->first();

            if (!$account) {
                return response()->json([
                    'success' => false,
                    'error' => 'Không tìm thấy tài khoản chính. Vui lòng thiết lập tài khoản chính.'
                ], 400);
            }

            $payload = [
                "USERNAME"  => $account->number_card,
                "PASSWORD"  => $account->password,
                "DAY_BEGIN" => Carbon::today()->format('d/m/Y'),
                "DAY_END"   => Carbon::today()->format('d/m/Y'),
                "NUMBER_MB" => $account->number_card
            ];

            Log::info('MB API Request Payload:', $payload);

            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://api-mb.dzmid.io.vn/api/transactions', [
                'json' => $payload,
            ]);

            $data = json_decode($response->getBody(), true);

            if ($data['success'] && isset($data['data']['transactionHistoryList'])) {
                $transactions = $data['data']['transactionHistoryList'];

                foreach ($transactions as $transaction) {
                    $amount = (float)($transaction['debitAmount'] ?: $transaction['creditAmount']);

                    try {
                        $transaction_date = Carbon::createFromFormat('d/m/Y H:i:s', $transaction['transactionDate'])->format('Y-m-d');
                    } catch (\Exception $e) {
                        Log::error('Error parsing transaction date: ' . $transaction['transactionDate']);
                        continue;
                    }

                    Log::info('Saving transaction:', [
                        'user_id' => auth()->user()->id,
                        'account_id' => $account->id,
                        'amount' => $amount,
                        'transaction_date' => $transaction_date,
                        'description' => $transaction['description'],
                        'address' => $transaction['addDescription'] ?? null
                    ]);

                    try {
                        Transaction::create([
                            'user_id' => auth()->user()->id,
                            'account_id' => $account->id,
                            'category_id' => null,
                            'amount' => $amount,
                            'transaction_date' => $transaction_date,
                            'type' => 'transfer',
                            'description' => $transaction['description'],
                            'address' => $transaction['addDescription'] ?? null,
                        ]);
                        Log::info('Transaction saved: ' . $transaction['refNo']);
                    } catch (\Exception $e) {
                        Log::error('Error saving transaction: ' . $e->getMessage());
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Transactions fetched and saved successfully.'
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Không có giao dịch nào để tải về.'
            ], 400);
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

    public function store(Request $request)
    {
        try {
            $account = Account::where('user_id', auth()->user()->id)
                ->where('is_primary', true)
                ->first();

            if (!$account) {
                return response()->json([
                    'success' => false,
                    'error' => 'Không tìm thấy tài khoản chính. Vui lòng thiết lập tài khoản chính.'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'account_id'       => 'nullable|exists:accounts,id',
                'category_id'      => 'nullable|exists:categories,id',
                'amount'           => 'required|numeric|min:0',
                'transaction_date' => 'nullable|date',
                'type'             => 'required|in:cash,transfer',
                'address' => 'nullable|string|max:255',
                'description'      => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ.',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            $data['user_id'] = auth()->user()->id;
            $data['transaction_date'] = $data['transaction_date'] ?? now()->toDateString();
            $data['account_id'] = $account->id;
            $transaction = Transaction::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Thêm giao dịch thành công.',
                'data'    => $transaction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Thêm giao dịch thành công.',
            ], 500);
        }
    }

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
