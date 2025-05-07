<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
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
                        Log::error('Lỗi khi phân tích ngày giao dịch: ' . $transaction['transactionDate']);
                        continue;
                    }

                    $transaction_type = $this->determineTransactionType($transaction['description'], $transaction['debitAmount'], $transaction['creditAmount']);

                    $category = $this->determineCategoryFromDescription($transaction['description']);
                    try {
                        Transaction::create([
                            'user_id' => auth()->user()->id,
                            'account_id' => $account->id,
                            'category_id' => $category ? $category->id : null,
                            'amount' => $amount,
                            'transaction_date' => $transaction_date,
                            'type' => 'transfer',
                            'transaction_type' => $transaction_type ?? null,
                            'description' => $transaction['description'],
                            'address' => $transaction['addDescription'] ?? null,
                        ]);
                        Log::info('Giao dịch đã được lưu: ' . $transaction['refNo']);
                    } catch (\Exception $e) {
                        Log::error('Lỗi khi lưu giao dịch: ' . $e->getMessage());
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Đã lấy và lưu giao dịch thành công.'
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Không có giao dịch nào để tải về.'
            ], 400);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Lỗi yêu cầu MB API: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Gửi yêu cầu tới MB Bank thất bại. Vui lòng thử lại sau.'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Lỗi chung MB API: ' . $e->getMessage());

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
                // 'transaction_type' => 'required|in:income,expense',
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
                'transaction_type'      => 'required|in:income,expense',
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


    public function determineTransactionType($description, $debitAmount, $creditAmount)
    {
        if ($creditAmount > 0) {
            return 'income';
        }

        if ($debitAmount > 0) {
            return 'expense';
        }

        $expenseKeywords = [
            'an',
            'chuyen tien',
            'mua',
            'thanh toan',
            'chi',
            'thuc pham',
            'thue',
            'giam gia',
            'rut tien',
            'dich vu',
            'thanh toán',
            'nha hang',
            'mua sắm',
            'cafe',
            'giam gia',
            'chi tiêu',
            'quà tặng'
        ];

        $incomeKeywords = [
            'thuong',
            'chuyen khoan',
            'deposit',
            'bonus',
            'thu',
            'thu nhap',
            'thuong lai',
            'lương',
            'tiền thưởng',
            'lãi suất',
            'thanh toán',
            'cổ tức',
            'tiền gửi',
            'thu nhập'
        ];

        foreach ($expenseKeywords as $keyword) {
            if (stripos($description, $keyword) !== false) {
                return 'expense';
            }
        }

        foreach ($incomeKeywords as $keyword) {
            if (stripos($description, $keyword) !== false) {
                return 'income';
            }
        }

        return null;
    }


    public function determineCategoryFromDescription($description)
    {
        $categoryKeywords = [
            'an-uong' => ['an trua', 'restaurant', 'cafe', 'di an'],
            'van-chuyen' => ['taxi', 'bus', 'xang', 'xe'],
            'mua-sam' => ['quan ao', 'dien tu', 'mua sam'],
            'giai-tri' => ['phim', 'su kien', 'game', 'am nhac', 'bai hat', 'hoat dong vui choi'],
            'hoa-don' => ['tien dien', 'nuoc', 'hoa don dien thoai'],
            'y-te' => ['thuoc', 'benh vien', 'kham'],
            'giao-duc' => ['hoc phi', 'truong', 'khoa hoc'],
            'dau-tu' => ['chung khoan', 'bat dong san', 'quy'],
            'vay-muon' => ['vay', 'lai suat', 'no'],
            'khac' => ['qua tang', 'tu thien', 'khoan khac'],
            'thuong' => ['bonus', 'thuong', 'khen thuong'],
            'chuyen-di' => ['tour', 'di du lich', 've may bay'],
            'tieu-dung' => ['thuc pham', 'do uong', 'gia dung', 'mua sam tieu dung'],
            'bao-hiem' => ['bao hiem', 'baohiem', 'bao hiem xe'],
            'tien-mat' => ['rut tien', 'nap tien', 'thanh toan tien mat'],
            'chuyen-khoan' => ['chuyen khoan', 'chuyen tien'],
            'mua-online' => ['mua hang', 'mua online', 'shopping online'],
            'thanh-toan-online' => ['pay', 'trang thanh toan', 'mua hang online'],
            'du-lich' => ['du lich', 'phuot', 'tour du lich'],
        ];

        foreach ($categoryKeywords as $categorySlug => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($description, $keyword) !== false) {
                    return Category::where('slug', $categorySlug)->first();
                }
            }
        }

        return null;
    }
}
