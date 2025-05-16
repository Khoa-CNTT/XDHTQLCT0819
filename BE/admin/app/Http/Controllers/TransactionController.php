<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\UserActivityLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use UserActivityLogger;

    public function index(Request $request)
    {
        try {
            $userId = auth()->id();
            $query = Transaction::where('user_id', $userId);

            if ($request->has('date')) {
                $date = Carbon::parse($request->input('date'))->format('Y-m-d');
                $query->whereDate('transaction_date', $date);
            }

            if ($request->has('month') && $request->has('year')) {
                $month = $request->input('month');
                $year = $request->input('year');
                $query->whereMonth('transaction_date', $month)
                    ->whereYear('transaction_date', $year);
            } else if ($request->has('year')) {
                $year = $request->input('year');
                $query->whereYear('transaction_date', $year);
            }

            if ($request->has('transaction_type')) {
                $transactionType = $request->input('transaction_type');
                $query->where('transaction_type', $transactionType);
            }

            $transactions = $query->get();

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải giao dịch: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Không thể lấy giao dịch tại thời điểm này. Vui lòng thử lại sau'
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'account_id'       => 'nullable|exists:accounts,id',
                'category_id'      => 'nullable|exists:categories,id',
                'amount'           => 'required|numeric|min:0|digits_between:1,20',
                'transaction_date' => 'nullable|date',
                'transaction_type' => 'nullable|in:income,expense',
                'type'             => 'required|in:cash,transfer',
                'address'          => 'nullable|string|max:255',
                'description'      => 'nullable|string',
            ], [
                'account_id.exists'       => 'Tài khoản đã chọn không hợp lệ.',
                'category_id.exists'      => 'Danh mục đã chọn không hợp lệ.',

                'amount.required'         => 'Vui lòng nhập số tiền.',
                'amount.numeric'          => 'Số tiền phải là một số.',
                'amount.min'              => 'Số tiền không được nhỏ hơn 0.',
                'amount.digits_between'   => 'Số tiền không được vượt quá 20 chữ số.',

                'transaction_date.date'   => 'Ngày giao dịch không hợp lệ.',

                'transaction_type.in'     => 'Loại giao dịch phải là thu nhập hoặc chi tiêu.',

                'type.required'           => 'Vui lòng chọn hình thức thanh toán.',
                'type.in'                 => 'Hình thức thanh toán không hợp lệ.',

                'address.string'          => 'Địa chỉ phải là chuỗi ký tự.',
                'address.max'             => 'Địa chỉ không được vượt quá 255 ký tự.',

                'description.string'      => 'Mô tả phải là chuỗi ký tự.',
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
            $data['transaction_date'] = $request->transaction_date ?? Carbon::now()->toDateString();


            $user = User::find(auth()->id());

            if ($request->category_id) {
                $category = Category::find($request->category_id);
                if ($category) {
                    $data['transaction_type'] = $category->type;
                    if ($category->type === 'income') {
                        $user->monthly_customer_spending += $request->amount;
                        $user->monthly_income += $request->amount;
                    } else if ($category->type === 'expense') {
                        $user->monthly_customer_spending -= $request->amount;
                    }
                    $user->save();
                }
            }

            $transaction = Transaction::create($data);
            $transaction->created_at = Carbon::parse($transaction->created_at)->addDay();
            $transaction->save();
            $this->logAction('Đã thêm giao dịch: ' . $transaction->description . ' thành công');

            return response()->json([
                'success' => true,
                'message' => 'Thêm giao dịch thành công.',
                'data'    => $transaction,
                'monthly_income' => $user->monthly_income,
                'monthly_customer_spending' => $user->monthly_customer_spending,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Đã xảy ra lỗi khi thêm giao dịch.',
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
                'account_id'       => 'nullable|exists:accounts,id',
                'category_id'      => 'nullable|exists:categories,id',
                'amount'           => 'required|numeric|min:0|digits_between:1,20',
                'transaction_date' => 'nullable|date',
                'transaction_type' => 'nullable|in:income,expense',
                'type'             => 'required|in:cash,transfer',
                'address'          => 'nullable|string|max:255',
                'description'      => 'nullable|string',
            ], [
                'account_id.exists'       => 'Tài khoản đã chọn không hợp lệ.',
                'category_id.exists'      => 'Danh mục đã chọn không hợp lệ.',

                'amount.required'         => 'Vui lòng nhập số tiền.',
                'amount.numeric'          => 'Số tiền phải là một số.',
                'amount.min'              => 'Số tiền không được nhỏ hơn 0.',
                'amount.digits_between'   => 'Số tiền không được vượt quá 20 chữ số.',

                'transaction_date.date'   => 'Ngày giao dịch không hợp lệ.',

                'transaction_type.in'     => 'Loại giao dịch phải là thu nhập hoặc chi tiêu.',

                'type.required'           => 'Vui lòng chọn hình thức thanh toán.',
                'type.in'                 => 'Hình thức thanh toán không hợp lệ.',

                'address.string'          => 'Địa chỉ phải là chuỗi ký tự.',
                'address.max'             => 'Địa chỉ không được vượt quá 255 ký tự.',

                'description.string'      => 'Mô tả phải là chuỗi ký tự.',
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
            $this->logAction('Đã cập nhật giao dịch: ' . $transaction->description . ' thành công');

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
            $user = User::find(auth()->id());

            if ($transaction->transaction_type === 'income') {
                $user->monthly_income -= $transaction->amount;
                $user->monthly_customer_spending -= $transaction->amount;
            } elseif ($transaction->transaction_type === 'expense') {
                $user->monthly_customer_spending += $transaction->amount;
            }

            $user->save();
            $transaction->delete();
            $this->logAction('Đã xoá giao dịch: ' . $transaction->description . ' thành công');
            return response()->json([
                'success' => true,
                'message' => 'Xóa giao dịch thành công.',
                'monthly_income' => $user->monthly_income,
                'monthly_customer_spending' => $user->monthly_customer_spending
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa giao dịch: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Không thể xóa giao dịch tại thời điểm này. Vui lòng thử lại sau.'
            ], 500);
        }
    }
}
