<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiController extends Controller
{

    public function CreatePrompt($text, $list_danh_muc)
    {
        $category_json_string = collect($list_danh_muc)
            ->map(fn($name, $id) => "$id: \"$name\"")
            ->implode(",\n    ");

        return <<<PROMPT
Câu đầu vào: "$text"

Yêu cầu: Trích xuất thông tin từ câu trên và trả về đúng định dạng JSON hợp lệ với các trường sau:

{
    "amount": (số tiền tính bằng đơn vị đồng, là số nguyên, ví dụ: 25000),
    "type": "cash" hoặc "transfer",
    "description": (mô tả ngắn gọn hành động chi tiêu, ví dụ: "Ăn bún bò", "Uống cà phê"),
    "category_id": (id của danh mục phù hợp, chọn từ các ID trong bảng dưới hoặc null nếu không phù hợp)
}

Dưới đây là danh sách danh mục để tham chiếu category_id:
{
    $category_json_string
}

Ghi nhớ:
- Trả về một chuỗi JSON hợp lệ duy nhất (double quotes, đúng cú pháp JSON).
- Không ghi chú thích, không giải thích thêm.
- Nếu không trích xuất được thì trả về đúng: null (chữ thường).
PROMPT;
    }

    public function AIVoid(Request $request)
    {
        $authUser = auth()->user();

        $list_danh_muc = Category::where('user_id', $authUser->id)->pluck('name', 'id')->toArray();

        $api_key = 'AIzaSyASMyZotUMFskPC3IMAbrPnKJq8Rm_sL-M';
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $api_key;

        $client = new Client([
            'base_uri' => 'https://generativelanguage.googleapis.com',
            'timeout'  => 30.0,
        ]);

        $text = $request->text;

        if (empty($text)) {
            return response()->json(['error' => true, 'message' => 'Bạn chưa nhập nội dung!'], 400);
        }

        $prompt = $this->CreatePrompt($text, $list_danh_muc);

        try {
            $response = $client->post($url, [
                'json' => [
                    'contents' => [[
                        'parts' => [['text' => $prompt]]
                    ]]
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            $ket_qua = trim($body['candidates'][0]['content']['parts'][0]['text'] ?? '');

            $clean = Str::of($ket_qua)->replace(["```json", "```"], '')->trim();
            $data = json_decode($clean, true);

            if (is_null($data)) {
                return response()->json(['error' => true, 'message' => 'AI không thể trích xuất dữ liệu!'], 422);
            }

            if (!isset($data['amount'], $data['type'], $data['description'])) {
                return response()->json(['error' => true, 'message' => 'Thiếu thông tin cần thiết trong phản hồi AI!'], 422);
            }

            $category_id = $data['category_id'] ?? null;
            $transaction_type = null;

            if (!is_null($category_id)) {
                $category = Category::find($category_id);
                if ($category && in_array($category->type, ['income', 'expense'])) {
                    $transaction_type = $category->type;
                }
            }

            if (Str::contains(Str::lower($data['description']), [
                'nhận',
                'thu nhập',
                'lương',
                'thưởng',
                'tiền về',
                'hoàn tiền',
                'đã nhận',
                'trả lại',
                'tiền lãi',
                'cổ tức',
                'tiền thưởng',
                'được trả',
                'nhận được',
                'đã gửi cho tôi',
                'chuyển đến',
                'chuyển vào',
                'hoàn trả',
                'trợ cấp',
                'phụ cấp',
                'nạp tiền',
                'nạp ví'
            ])) {
                $transaction_type = 'income';
            } elseif (Str::contains(Str::lower($data['description']), [
                'chi tiêu',
                'mua',
                'trả',
                'chuyển tiền',
                'thanh toán',
                'chuyển khoản',
                'đóng phí',
                'phí',
                'tiền điện',
                'tiền nước',
                'tiền nhà',
                'tiền phòng',
                'gửi tiền',
                'chuyển đi',
                'trả nợ',
                'mua sắm',
                'ăn uống',
                'cà phê',
                'nhà hàng',
                'siêu thị',
                'xăng dầu',
                'đi chợ',
                'đặt hàng',
                'mua vé',
                'thuê',
                'đặt cọc',
                'rút tiền',
                'chuyển cho',
                'gửi cho',
                'chi trả',
                'trả góp',
                'học phí',
                'viện phí',
                'bệnh viện',
                'thuốc',
                'khám bệnh',
                'trà sữa',
                'bánh mì',
                'đồ ăn',
                'đám'

            ])) {
                $transaction_type = 'expense';
            }

            if (is_null($transaction_type)) {
                return response()->json(['error' => true, 'message' => 'Không thể xác định loại giao dịch!'], 422);
            }

            DB::table('users')->where('id', $authUser->id)->update([
                'monthly_income' => $transaction_type === 'income'
                    ? DB::raw('monthly_income + ' . $data['amount'])
                    : DB::raw('monthly_income'),
                'monthly_customer_spending' => $transaction_type === 'income'
                    ? DB::raw('monthly_customer_spending + ' . $data['amount'])
                    : DB::raw('monthly_customer_spending - ' . $data['amount'])
            ]);

            $transaction = Transaction::create([
                'user_id'          => $authUser->id,
                'category_id'      => $category_id,
                'amount'           => $data['amount'],
                'transaction_date' => Carbon::now()->format('Y-m-d'),
                'type'             => $data['type'],
                'transaction_type' => $transaction_type,
                'description'      => $data['description'],
            ]);

            $updatedUser = DB::table('users')->where('id', $authUser->id)->first();

            return response()->json([
                'success' => true,
                'message' => 'Giao dịch đã được tạo thành công!',
                'data'    => $transaction,
                'monthly_income' => $updatedUser->monthly_income,
                'monthly_customer_spending' => $updatedUser->monthly_customer_spending,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Lỗi khi tạo giao dịch: ' . $e->getMessage()
            ], 500);
        }
    }


    // MBank fetch

    public function fetchAndClassifyMBBankTransactions()
    {
        try {
            $authUser = auth()->user();
            $list_danh_muc = Category::where('user_id', $authUser->id)->pluck('name', 'id')->toArray();

            $account = Account::where('user_id', $authUser->id)
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
                "PASSWORD"  => Crypt::decryptString($account->password),
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
                $processedCount = 0;

                foreach ($transactions as $transaction) {
                    $amount = (float)($transaction['debitAmount'] ?: $transaction['creditAmount']);

                    try {
                        $transaction_date = Carbon::createFromFormat('d/m/Y H:i:s', $transaction['transactionDate'])->format('Y-m-d');
                    } catch (\Exception $e) {
                        Log::error('Lỗi khi phân tích ngày giao dịch: ' . $transaction['transactionDate']);
                        continue;
                    }

                    $transaction_type = !empty($transaction['creditAmount']) ? 'income' : 'expense';

                    $transaction_info = $this->analyzeTransaction($transaction['description'], $list_danh_muc);

                    if ($transaction_info) {
                        try {
                            DB::table('users')->where('id', $authUser->id)->update([
                                'monthly_income' => $transaction_type === 'income'
                                    ? DB::raw('monthly_income + ' . $amount)
                                    : DB::raw('monthly_income'),
                                'monthly_customer_spending' => $transaction_type === 'income'
                                    ? DB::raw('monthly_customer_spending + ' . $amount)
                                    : DB::raw('monthly_customer_spending - ' . $amount)
                            ]);

                            Transaction::create([
                                'user_id' => $authUser->id,
                                'account_id' => $account->id,
                                'category_id' => $transaction_info['category_id'],
                                'amount' => $amount,
                                'transaction_date' => $transaction_date,
                                'type' => $transaction_info['type'],
                                'transaction_type' => $transaction_type,
                                'description' => $transaction_info['description'] ?? $transaction['description'],
                                'address' => $transaction['addDescription'] ?? null,
                            ]);

                            $processedCount++;
                            Log::info('Giao dịch đã được phân loại và lưu: ' . $transaction['refNo']);
                        } catch (\Exception $e) {
                            Log::error('Lỗi khi lưu giao dịch: ' . $e->getMessage());
                        }
                    }
                }

                $updatedUser = DB::table('users')->where('id', $authUser->id)->first();

                return response()->json([
                    'success' => true,
                    'message' => 'Đã tải và phân loại ' . $processedCount . ' giao dịch thành công.',
                    'monthly_income' => $updatedUser->monthly_income,
                    'monthly_customer_spending' => $updatedUser->monthly_customer_spending,
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

    private function analyzeTransaction($description, $list_danh_muc)
    {
        $api_key = 'AIzaSyASMyZotUMFskPC3IMAbrPnKJq8Rm_sL-M';
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $api_key;

        $client = new Client([
            'base_uri' => 'https://generativelanguage.googleapis.com',
            'timeout'  => 30.0,
        ]);

        $prompt = $this->createTransactionPrompt($description, $list_danh_muc);

        try {
            $response = $client->post($url, [
                'json' => [
                    'contents' => [[
                        'parts' => [['text' => $prompt]]
                    ]]
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            $result = trim($body['candidates'][0]['content']['parts'][0]['text'] ?? '');

            $clean = Str::of($result)->replace(["```json", "```"], '')->trim();
            $data = json_decode($clean, true);

            if (is_null($data)) {
                Log::warning('AI không thể trích xuất dữ liệu từ mô tả: ' . $description);
                return null;
            }

            if (!isset($data['amount'], $data['description'])) {
                Log::warning('Thiếu thông tin cần thiết trong phản hồi AI cho mô tả: ' . $description);
                return null;
            }

            $data['type'] = 'transfer';

            $transaction_type = $this->determineTransactionType($data['description']);
            $data['transaction_type'] = $transaction_type;

            return $data;
        } catch (\Exception $e) {
            Log::error('Lỗi khi phân tích giao dịch: ' . $e->getMessage());
            return null;
        }
    }

    private function createTransactionPrompt($description, $list_danh_muc)
    {
        $category_json_string = collect($list_danh_muc)
            ->map(fn($name, $id) => "$id: \"$name\"")
            ->implode(",\n    ");

        return <<<PROMPT
Mô tả giao dịch: "$description"

Yêu cầu: Trích xuất thông tin từ mô tả giao dịch trên và trả về đúng định dạng JSON hợp lệ với các trường sau:

{
    "amount": (số tiền tính bằng đơn vị đồng, là số nguyên, lấy từ mô tả nếu có hoặc để 0),
    "type": "transfer",
    "description": (mô tả ngắn gọn hành động chi tiêu hoặc thu nhập, tối đa 50 ký tự),
    "category_id": (id của danh mục phù hợp, chọn từ các ID trong bảng dưới hoặc null nếu không phù hợp)
}

Dưới đây là danh sách danh mục để tham chiếu category_id:
{
    $category_json_string
}

Ghi nhớ:
- Trả về một chuỗi JSON hợp lệ duy nhất (double quotes, đúng cú pháp JSON).
- Không ghi chú thích, không giải thích thêm.
- Nếu không trích xuất được thì trả về đúng: null (chữ thường).
PROMPT;
    }

    private function determineTransactionType($description)
    {
        $description = Str::lower($description);

        $income_keywords = [
            'nhận',
            'thu nhập',
            'lương',
            'thưởng',
            'tiền về',
            'hoàn tiền',
            'đã nhận',
            'trả lại',
            'tiền lãi',
            'cổ tức',
            'tiền thưởng',
            'được trả',
            'nhận được',
            'đã gửi cho tôi',
            'chuyển đến',
            'chuyển vào',
            'hoàn trả',
            'trợ cấp',
            'phụ cấp',
            'nạp tiền',
            'nạp ví',
            'chuyen tien',
            'mbvcb',
            'khuyen mai',
            'hoan tra',
            'tiet kiem',
            'thu'
        ];

        $expense_keywords = [
            'chi tiêu',
            'mua',
            'trả',
            'chuyển tiền',
            'thanh toán',
            'chuyển khoản',
            'đóng phí',
            'phí',
            'tiền điện',
            'tiền nước',
            'tiền nhà',
            'tiền phòng',
            'gửi tiền',
            'chuyển đi',
            'trả nợ',
            'mua sắm',
            'ăn uống',
            'cà phê',
            'nhà hàng',
            'siêu thị',
            'xăng dầu',
            'đi chợ',
            'đặt hàng',
            'mua vé',
            'thuê',
            'đặt cọc',
            'rút tiền',
            'chuyển cho',
            'gửi cho',
            'chi trả',
            'trả góp',
            'học phí',
            'viện phí',
            'bệnh viện',
            'thuốc',
            'khám bệnh',
            'trà sữa',
            'bánh mì',
            'đồ ăn',
            'chi',
            'napas',
            'gd',
            'an',
            'toi',
            'trua',
            'sang',
            'coffee',
            'bank',
            'rut tien',
            'viettel',
            'sach',
            'chuyen di',
            'giai tri',
            'du lich',
            'khach san',
            'xe',
            'xang',
            'sua chua',
            'bao tri',
            'dien thoai',
            'internet',
            'bao hiem',
            'thue',
            'thuong mai',
            'mua sam',
            'quan ao'
        ];

        foreach ($income_keywords as $keyword) {
            if (Str::contains($description, $keyword)) {
                return 'income';
            }
        }

        foreach ($expense_keywords as $keyword) {
            if (Str::contains($description, $keyword)) {
                return 'expense';
            }
        }

        return 'expense';
    }


    // CHAT BOX

    public function CreatePromptChatBox($context = null)
    {
        return <<<PROMPT
Bạn là trợ lý tài chính cá nhân thông minh. Hãy trả lời câu hỏi người dùng dựa vào thông tin được cung cấp.

Thông tin của người dùng:
{{USER_INFO}}

Lịch sử giao dịch gần đây:
{{TRANSACTION_HISTORY}}

Phân loại giao dịch theo danh mục:
{{CATEGORY_STATISTICS}}

Câu hỏi: "{{USER_QUESTION}}"

Lưu ý:
- Trả lời ngắn gọn, rõ ràng và hữu ích
- Sử dụng ngôn ngữ thân thiện
- Đưa ra lời khuyên hoặc gợi ý (nếu phù hợp)
- Không sử dụng từ ngữ quá kỹ thuật
- Trả lời bằng tiếng Việt
PROMPT;
    }

    public function chatBox(Request $request)
    {
        $authUser = auth()->user();
        $userQuestion = $request->message;

        if (empty($userQuestion)) {
            return response()->json(['error' => true, 'message' => 'Bạn chưa nhập câu hỏi!'], 400);
        }

        try {
            $userInfo = DB::table('users')->where('id', $authUser->id)->first();
            $userInfoText = "- Thu nhập hàng tháng: " . number_format($userInfo->monthly_income) . " đồng\n";
            $userInfoText .= "- Chi tiêu hàng tháng: " . number_format($userInfo->monthly_customer_spending) . " đồng\n";

            $startDate = Carbon::now()->subMonths(3)->startOfMonth();
            $transactions = Transaction::where('user_id', $authUser->id)
                ->where('transaction_date', '>=', $startDate)
                ->with('category')
                ->orderBy('transaction_date', 'desc')
                ->get();

            $transactionHistory = "";
            foreach ($transactions as $index => $transaction) {
                if ($index < 10) {
                    $transactionHistory .= "- " . Carbon::parse($transaction->transaction_date)->format('d/m/Y') . ": ";
                    $transactionHistory .= ($transaction->transaction_type == 'income' ? "+" : "-") . number_format($transaction->amount) . "đ ";
                    $transactionHistory .= "(" . $transaction->description . ")";
                    $transactionHistory .= $transaction->category ? " [" . $transaction->category->name . "]" : "";
                    $transactionHistory .= "\n";
                }
            }

            $lastThreeMonths = [];

            for ($i = 2; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $lastThreeMonths[$month->format('Y-m')] = [
                    'label' => $month->format('m/Y'),
                    'categories' => []
                ];
            }

            foreach ($transactions as $transaction) {
                $monthKey = Carbon::parse($transaction->transaction_date)->format('Y-m');

                if (isset($lastThreeMonths[$monthKey]) && $transaction->category_id) {
                    $categoryName = $transaction->category ? $transaction->category->name : 'Khác';
                    $categoryId = $transaction->category_id;

                    if (!isset($lastThreeMonths[$monthKey]['categories'][$categoryId])) {
                        $lastThreeMonths[$monthKey]['categories'][$categoryId] = [
                            'name' => $categoryName,
                            'income' => 0,
                            'expense' => 0
                        ];
                    }

                    if ($transaction->transaction_type == 'income') {
                        $lastThreeMonths[$monthKey]['categories'][$categoryId]['income'] += $transaction->amount;
                    } else {
                        $lastThreeMonths[$monthKey]['categories'][$categoryId]['expense'] += $transaction->amount;
                    }
                }
            }

            $categoryStatisticsText = "";
            foreach ($lastThreeMonths as $monthKey => $monthData) {
                $categoryStatisticsText .= "Tháng " . $monthData['label'] . ":\n";

                if (empty($monthData['categories'])) {
                    $categoryStatisticsText .= "- Không có dữ liệu\n";
                } else {
                    foreach ($monthData['categories'] as $categoryId => $categoryData) {
                        if ($categoryData['expense'] > 0) {
                            $categoryStatisticsText .= "- " . $categoryData['name'] . ": -" . number_format($categoryData['expense']) . "đ\n";
                        }
                        if ($categoryData['income'] > 0) {
                            $categoryStatisticsText .= "- " . $categoryData['name'] . ": +" . number_format($categoryData['income']) . "đ\n";
                        }
                    }
                }
                $categoryStatisticsText .= "\n";
            }

            // Tạo prompt
            $prompt = $this->CreatePromptChatBox();
            $prompt = str_replace(
                ['{{USER_INFO}}', '{{TRANSACTION_HISTORY}}', '{{CATEGORY_STATISTICS}}', '{{USER_QUESTION}}'],
                [$userInfoText, $transactionHistory, $categoryStatisticsText, $userQuestion],
                $prompt
            );

            $api_key = 'AIzaSyASMyZotUMFskPC3IMAbrPnKJq8Rm_sL-M';
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $api_key;

            $client = new Client([
                'base_uri' => 'https://generativelanguage.googleapis.com',
                'timeout'  => 30.0,
            ]);

            $response = $client->post($url, [
                'json' => [
                    'contents' => [[
                        'parts' => [['text' => $prompt]]
                    ]]
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            $botResponse = trim($body['candidates'][0]['content']['parts'][0]['text'] ?? 'Tôi không thể trả lời câu hỏi này lúc này.');

            return response()->json([
                'success' => true,
                'question' => $userQuestion,
                'answer' => $botResponse
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Lỗi khi xử lý: ' . $e->getMessage()
            ], 500);
        }
    }
}
