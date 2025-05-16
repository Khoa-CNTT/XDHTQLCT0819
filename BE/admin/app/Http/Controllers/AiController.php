<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            - Nếu người dùng muốn thực hiện các hành động sau, hãy trả về theo định dạng JSON đặc biệt:
            - Thêm danh mục: {"action":"add_category","category_name":"Tên danh mục","type":"income hoặc expense"}
            - Thêm giao dịch: {"action":"add_transaction","category_id":ID,"amount":TIỀN,"description":"MÔ TẢ","transaction_type":"income/expense"}
            - Xóa danh mục: {"action":"delete_category","category_id":ID}
            - Xóa danh mục và giao dịch: {"action":"delete_category_with_transactions","category_id":ID}
            - Cập nhật tên danh mục: {"action":"update_category","category_id":ID,"new_name":"Tên mới"}
            - Xóa giao dịch: {"action":"delete_transaction","transaction_id":ID}
            PROMPT;
                }


    public function chatBox(Request $request)
    {
        $authUser = Auth::user();
        $userQuestion = $request->message;

        if (empty($userQuestion)) {
            return response()->json(['error' => true, 'message' => 'Bạn chưa nhập câu hỏi!'], 400);
        }

        try {
            $userInfo = DB::table('users')->where('id', $authUser->id)->first();

            $currentMonth = Carbon::now()->startOfMonth();
            $totalExpense = Transaction::where('user_id', $authUser->id)
                ->where('transaction_type', 'expense')
                ->where('transaction_date', '>=', $currentMonth)
                ->sum('amount');

            $userInfoText = "Thông tin người dùng:\n";
            $userInfoText .= "- Thu nhập hiện tại: " . number_format($userInfo->monthly_income) . " đồng\n";
            $userInfoText .= "- Tổng chi tiêu tháng này: " . number_format($totalExpense) . " đồng\n";
            $userInfoText .= "- Số dư hiện tại: " . number_format($userInfo->monthly_customer_spending) . " đồng\n";

            foreach ((array) $userInfo as $key => $value) {
                $userInfoText .= "- " . ucfirst($key) . ": " . $value . "\n";
            }

            $categories = Category::where('user_id', $authUser->id)
                ->orWhereNull('user_id')
                ->select('id', 'name', 'type')
                ->get();

            $categoriesText = "Danh mục hiện có:\n";
            foreach ($categories as $category) {
                $categoriesText .= "- ID: " . $category->id . ", Tên: " . $category->name . ", Loại: " . $category->type . "\n";
            }
            $userInfoText .= "\n" . $categoriesText;

            $startDate = Carbon::now()->subMonths(3)->startOfMonth();
            $transactions = Transaction::where('user_id', $authUser->id)
                ->where('transaction_date', '>=', $startDate)
                ->with('category')
                ->orderBy('transaction_date', 'desc')
                ->get();

            $transactionHistory = "";
            foreach ($transactions as $index => $transaction) {
                if ($index < 10) {
                    $transactionHistory .= "- ID: " . $transaction->id . " - ";
                    $transactionHistory .= Carbon::parse($transaction->transaction_date)->format('d/m/Y') . ": ";
                    $transactionHistory .= $transaction->amount;
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
                        $categoryStatisticsText .= "- " . $categoryData['name'] . ": -" . number_format($categoryData['expense']) . "đ\n";
                        $categoryStatisticsText .= "- " . $categoryData['name'] . ": +" . number_format($categoryData['income']) . "đ\n";
                    }
                }
                $categoryStatisticsText .= "\n";
            }

            // 3. Kiểm tra nếu người dùng muốn thêm danh mục mới
            if (preg_match('/thêm danh mục|tạo danh mục|danh mục mới/i', strtolower($userQuestion))) {
                // Trích xuất tên danh mục từ câu hỏi
                preg_match('/["\']([^"\']+)["\']|tên (?:là|:)\s*([^\s,\.]+)|danh mục\s+([^\s,\.]+)/i', $userQuestion, $matches);
                $categoryName = $matches[1] ?? $matches[2] ?? $matches[3] ?? null;

                preg_match('/loại\s*(?:là|:)?\s*(thu nhập|chi tiêu|income|expense)/i', $userQuestion, $typeMatches);
                $categoryType = $typeMatches[1] ?? null;

                if ($categoryType) {
                    $categoryType = strtolower($categoryType);
                    if ($categoryType === 'thu nhập' || $categoryType === 'income') {
                        $categoryType = 'income';
                    } else {
                        $categoryType = 'expense';
                    }
                } else {
                    $categoryType = 'expense';
                }

                if ($categoryName) {
                    $newCategory = new Category();
                    $newCategory->name = $categoryName;
                    $newCategory->user_id = $authUser->id;
                    $newCategory->slug = Str::slug($categoryName);
                    $newCategory->type = $categoryType;
                    $newCategory->save();

                    return response()->json([
                        'success' => true,
                        'question' => $userQuestion,
                        'answer' => "Đã thêm danh mục mới thành công! ",
                        'action' => 'add_category',
                        'category' => [
                            'id' => $newCategory->id,
                            'name' => $newCategory->name,
                            'slug' => $newCategory->slug,
                            'type' => $newCategory->type
                        ]
                    ]);
                }
            }

            // 4. Kiểm tra nếu người dùng muốn thêm giao dịch mới
            if (preg_match('/thêm giao dịch|tạo giao dịch|giao dịch mới/i', strtolower($userQuestion))) {
                preg_match('/danh mục (?:id|ID)?\s*:?\s*(\d+)/i', $userQuestion, $categoryMatches);
                preg_match('/số tiền\s*:?\s*(\d+(?:[,.]\d+)?)/i', $userQuestion, $amountMatches);
                preg_match('/mô tả\s*:?\s*["\']([^"\']+)["\']|mô tả\s*:?\s*(\S+)/i', $userQuestion, $descMatches);
                preg_match('/loại\s*:?\s*(thu nhập|chi tiêu|income|expense)/i', $userQuestion, $typeMatches);

                $categoryId = $categoryMatches[1] ?? null;
                $amount = $amountMatches[1] ?? null;
                $description = $descMatches[1] ?? $descMatches[2] ?? null;
                $type = $typeMatches[1] ?? null;

                if ($type) {
                    $type = strtolower($type);
                    $type = ($type === 'thu nhập' || $type === 'income') ? 'income' : 'expense';
                }

                if ($categoryId && $amount && $description && $type) {
                    $newTransaction = new Transaction();
                    $newTransaction->user_id = $authUser->id;
                    $newTransaction->category_id = $categoryId;
                    $newTransaction->amount = $amount;
                    $newTransaction->description = $description;
                    $newTransaction->transaction_type = $type;
                    $newTransaction->transaction_date = Carbon::now();
                    $newTransaction->type = 'cash';
                    $newTransaction->save();

                    $amountValue = $amount;
                    if ($type === 'income') {
                        $authUser->monthly_income += $amountValue;
                        $authUser->monthly_customer_spending += $amountValue;
                    } else {
                        $authUser->monthly_customer_spending -= $amountValue;
                    }
                    User::where('id', $authUser->id)->update([
                        'monthly_income' => $authUser->monthly_income,
                        'monthly_customer_spending' => $authUser->monthly_customer_spending,
                    ]);

                    return response()->json([
                        'success' => true,
                        'question' => $userQuestion,
                        'answer' => "Đã thêm giao dịch mới thành công!",
                        'action' => 'add_transaction',
                        'transaction' => [
                            'id' => $newTransaction->id,
                            'amount' => $newTransaction->amount,
                            'description' => $newTransaction->description,
                            'type' => $newTransaction->transaction_type,
                            'category_id' => $newTransaction->category_id
                        ]
                    ]);
                }
            }


            // 5. CHỨC NĂNG MỚI: Xóa danh mục (và các giao dịch liên quan nếu có)
            if (preg_match('/xóa danh mục|xoa danh muc|xoá danh mục/i', strtolower($userQuestion))) {
                preg_match('/(?:id|ID)?\s*:?\s*(\d+)/i', $userQuestion, $categoryMatches);
                $categoryId = $categoryMatches[1] ?? null;

                $deleteTransactions = preg_match('/xóa giao dịch liên quan|xóa tất cả giao dịch|xoá cả giao dịch/i', strtolower($userQuestion));

                if ($categoryId) {
                    $category = Category::where('id', $categoryId)
                        ->where('user_id', $authUser->id)
                        ->first();

                    if ($category) {
                        $relatedTransactions = Transaction::where('category_id', $categoryId)
                            ->where('user_id', $authUser->id)
                            ->get();

                        $transactionCount = $relatedTransactions->count();

                        if ($transactionCount > 0) {
                            if ($deleteTransactions) {
                                $incomeTotal = 0;
                                $expenseTotal = 0;

                                foreach ($relatedTransactions as $transaction) {
                                    $amount =  $transaction->amount;
                                    if ($transaction->transaction_type === 'income') {
                                        $incomeTotal += $amount;
                                    } else {
                                        $expenseTotal += $amount;
                                    }
                                }

                                $newIncome = max(0, $authUser->monthly_income - $incomeTotal);
                                $newSpending = max(0, $authUser->monthly_customer_spending - $incomeTotal + $expenseTotal);

                                User::where('id', $authUser->id)->update([
                                    'monthly_income' => $newIncome,
                                    'monthly_customer_spending' => $newSpending
                                ]);

                                Transaction::where('category_id', $categoryId)
                                    ->where('user_id', $authUser->id)
                                    ->delete();

                                $category->delete();

                                return response()->json([
                                    'success' => true,
                                    'question' => $userQuestion,
                                    'answer' => "Đã xóa danh mục và {$transactionCount} giao dịch liên quan thành công!",
                                    'action' => 'delete_category_with_transactions',
                                    'category_id' => $categoryId,
                                    'transactions_deleted' => $transactionCount
                                ]);
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'question' => $userQuestion,
                                    'answer' => "Danh mục này có {$transactionCount} giao dịch liên quan. Bạn có muốn xóa cả danh mục và các giao dịch liên quan không? Nếu có, vui lòng gõ 'xóa danh mục ID: {$categoryId} và xóa giao dịch liên quan'.",
                                ]);
                            }
                        } else {
                            $category->delete();

                            return response()->json([
                                'success' => true,
                                'question' => $userQuestion,
                                'answer' => "Đã xóa danh mục thành công!",
                                'action' => 'delete_category',
                                'category_id' => $categoryId
                            ]);
                        }
                    } else {
                        return response()->json([
                            'success' => false,
                            'question' => $userQuestion,
                            'answer' => "Không tìm thấy danh mục với ID: {$categoryId} hoặc danh mục không thuộc về bạn.",
                        ]);
                    }
                }
            }


            // 6. CHỨC NĂNG MỚI: Cập nhật tên danh mục
            if (preg_match('/cập nhật danh mục|cap nhat danh muc|đổi tên danh mục|doi ten danh muc/i', strtolower($userQuestion))) {
                preg_match('/(?:id|ID)?\s*:?\s*(\d+)/i', $userQuestion, $categoryMatches);
                preg_match('/tên mới\s*:?\s*["\']([^"\']+)["\']|tên mới\s*:?\s*(\S+)/i', $userQuestion, $nameMatches);

                $categoryId = $categoryMatches[1] ?? null;
                $newName = $nameMatches[1] ?? $nameMatches[2] ?? null;

                if ($categoryId && $newName) {
                    // Kiểm tra xem danh mục có thuộc về người dùng không
                    $category = Category::where('id', $categoryId)
                        ->where('user_id', $authUser->id)
                        ->first();

                    if ($category) {
                        // Cập nhật tên danh mục
                        $category->name = $newName;
                        $category->slug = Str::slug($newName);
                        $category->save();

                        return response()->json([
                            'success' => true,
                            'question' => $userQuestion,
                            'answer' => "Đã cập nhật tên danh mục thành công!",
                            'action' => 'update_category',
                            'category' => [
                                'id' => $category->id,
                                'name' => $category->name,
                                'slug' => $category->slug,
                                'type' => $category->type
                            ]
                        ]);
                    } else {
                        return response()->json([
                            'success' => false,
                            'question' => $userQuestion,
                            'answer' => "Không tìm thấy danh mục với ID: " . $categoryId . " hoặc danh mục không thuộc về bạn.",
                        ]);
                    }
                }
            }

            // 7. CHỨC NĂNG MỚI: Xóa giao dịch
            if (preg_match('/xóa giao dịch|xoa giao dich|xoá giao dịch/i', strtolower($userQuestion))) {
                preg_match('/(?:id|ID)?\s*:?\s*(\d+)/i', $userQuestion, $transactionMatches);
                $transactionId = $transactionMatches[1] ?? null;

                if ($transactionId) {
                    $transaction = Transaction::where('id', $transactionId)
                        ->where('user_id', $authUser->id)
                        ->first();

                    if ($transaction) {
                        $amount =  $transaction->amount;
                        $type = $transaction->transaction_type;

                        $newIncome = $authUser->monthly_income;
                        $newSpending = $authUser->monthly_customer_spending;

                        if ($type === 'income') {
                            $newIncome = max(0, $newIncome - $amount);
                            $newSpending = max(0, $newSpending - $amount);
                        } else {
                            $newSpending = max(0, $newSpending + $amount);
                        }

                        User::where('id', $authUser->id)->update([
                            'monthly_income' => $newIncome,
                            'monthly_customer_spending' => $newSpending
                        ]);

                        $transaction->delete();

                        return response()->json([
                            'success' => true,
                            'question' => $userQuestion,
                            'answer' => "Đã xóa giao dịch thành công!",
                            'action' => 'delete_transaction',
                            'transaction_id' => $transactionId
                        ]);
                    } else {
                        return response()->json([
                            'success' => false,
                            'question' => $userQuestion,
                            'answer' => "Không tìm thấy giao dịch với ID: " . $transactionId . " hoặc giao dịch không thuộc về bạn.",
                        ]);
                    }
                }
            }


            // 8. Tạo prompt cho AI
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

            // 9. Kiểm tra nếu phản hồi của bot có chứa JSON đặc biệt
            if (strpos($botResponse, '{"action":') !== false) {
                preg_match('/({.*})/', $botResponse, $matches);
                if (isset($matches[1])) {
                    $actionData = json_decode($matches[1], true);

                    if ($actionData && isset($actionData['action'])) {
                        if ($actionData['action'] === 'add_category' && isset($actionData['category_name'])) {
                            $categoryType = $actionData['type'] ?? 'expense';

                            $newCategory = new Category();
                            $newCategory->name = $actionData['category_name'];
                            $newCategory->user_id = $authUser->id;
                            $newCategory->slug = Str::slug($actionData['category_name']);
                            $newCategory->type = $categoryType;
                            $newCategory->save();

                            return response()->json([
                                'success' => true,
                                'question' => $userQuestion,
                                'answer' => "Đã thêm danh mục mới thành công!",
                                'action' => 'add_category',
                                'category' => [
                                    'id' => $newCategory->id,
                                    'name' => $newCategory->name,
                                    'slug' => $newCategory->slug,
                                    'type' => $newCategory->type
                                ]
                            ]);
                        }

                        // Xoá giao dịch
                        else if (
                            $actionData['action'] === 'add_transaction' &&
                            isset($actionData['category_id']) &&
                            isset($actionData['amount']) &&
                            isset($actionData['description']) &&
                            isset($actionData['transaction_type'])
                        ) {
                            $newTransaction = new Transaction();
                            $newTransaction->user_id = $authUser->id;
                            $newTransaction->category_id = $actionData['category_id'];
                            $newTransaction->amount = $actionData['amount'];
                            $newTransaction->description = $actionData['description'];
                            $newTransaction->transaction_type = $actionData['transaction_type'];
                            $newTransaction->transaction_date = Carbon::now();
                            $newTransaction->type = 'cash';
                            $newTransaction->save();

                            $amountValue = $actionData['amount'];

                            if ($newTransaction->transaction_type === 'income') {
                                $authUser->monthly_income += $amountValue;
                                $authUser->monthly_customer_spending += $amountValue;
                            } else if ($newTransaction->transaction_type === 'expense') {
                                $authUser->monthly_customer_spending -= $amountValue;
                            }

                            User::where('id', $authUser->id)->update([
                                'monthly_income' => $authUser->monthly_income,
                                'monthly_customer_spending' => $authUser->monthly_customer_spending,
                            ]);

                            return response()->json([
                                'success' => true,
                                'question' => $userQuestion,
                                'answer' => "Đã thêm giao dịch mới thành công!",
                                'action' => 'add_transaction',
                                'transaction' => [
                                    'id' => $newTransaction->id,
                                    'amount' => $newTransaction->amount,
                                    'description' => $newTransaction->description,
                                    'type' => $newTransaction->transaction_type,
                                    'category_id' => $newTransaction->category_id
                                ]
                            ]);
                        }

                        // xoá danh mục với các giao dịch liên quan
                        else if ($actionData['action'] === 'delete_category_with_transactions' && isset($actionData['category_id'])) {
                            $categoryId = $actionData['category_id'];

                            $category = Category::where('id', $categoryId)
                                ->where('user_id', $authUser->id)
                                ->first();

                            if ($category) {
                                $transactions = Transaction::where('category_id', $categoryId)
                                    ->where('user_id', $authUser->id)
                                    ->get();

                                $transactionCount = $transactions->count();

                                foreach ($transactions as $transaction) {
                                    $amountValue = $transaction->amount;
                                    if ($transaction->transaction_type === 'income') {
                                        $authUser->monthly_income -= $amountValue;
                                        $authUser->monthly_customer_spending -= $amountValue;
                                    } else if ($transaction->transaction_type === 'expense') {
                                        $authUser->monthly_customer_spending += $amountValue;
                                    }

                                    $transaction->delete();
                                }

                                $category->delete();

                                User::where('id', $authUser->id)->update([
                                    'monthly_income' => $authUser->monthly_income,
                                    'monthly_customer_spending' => $authUser->monthly_customer_spending,
                                ]);

                                return response()->json([
                                    'success' => true,
                                    'question' => $userQuestion,
                                    'answer' => "Đã xóa danh mục và " . $transactionCount . " giao dịch liên quan thành công!",
                                    'action' => 'delete_category_with_transactions',
                                    'category_id' => $categoryId,
                                    'transactions_deleted' => $transactionCount
                                ]);
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'question' => $userQuestion,
                                    'answer' => "Không tìm thấy danh mục với ID: " . $categoryId . " hoặc danh mục không thuộc về bạn.",
                                ]);
                            }
                        }

                        // cập nhật tên danh mục
                        else if ($actionData['action'] === 'update_category' && isset($actionData['category_id']) && isset($actionData['new_name'])) {
                            $categoryId = $actionData['category_id'];
                            $newName = $actionData['new_name'];

                            $category = Category::where('id', $categoryId)
                                ->where('user_id', $authUser->id)
                                ->first();

                            if ($category) {
                                $category->name = $newName;
                                $category->slug = Str::slug($newName);
                                $category->save();

                                return response()->json([
                                    'success' => true,
                                    'question' => $userQuestion,
                                    'answer' => "Đã cập nhật tên danh mục thành công!",
                                    'action' => 'update_category',
                                    'category' => [
                                        'id' => $category->id,
                                        'name' => $category->name,
                                        'slug' => $category->slug,
                                        'type' => $category->type
                                    ]
                                ]);
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'question' => $userQuestion,
                                    'answer' => "Không tìm thấy danh mục với ID: " . $categoryId . " hoặc danh mục không thuộc về bạn.",
                                ]);
                            }
                        }

                        // xoá giao dịch
                        else if ($actionData['action'] === 'delete_transaction' && isset($actionData['transaction_id'])) {
                            $transactionId = $actionData['transaction_id'];

                            $transaction = Transaction::where('id', $transactionId)
                                ->where('user_id', $authUser->id)
                                ->first();

                            if ($transaction) {
                                $amountValue = $transaction->amount;

                                if ($transaction->transaction_type === 'income') {
                                    $authUser->monthly_income -= $amountValue;
                                    $authUser->monthly_customer_spending -= $amountValue;
                                } else if ($transaction->transaction_type === 'expense') {
                                    $authUser->monthly_customer_spending += $amountValue;
                                }

                                $transaction->delete();

                                User::where('id', $authUser->id)->update([
                                    'monthly_income' => $authUser->monthly_income,
                                    'monthly_customer_spending' => $authUser->monthly_customer_spending,
                                ]);

                                return response()->json([
                                    'success' => true,
                                    'question' => $userQuestion,
                                    'answer' => "Đã xóa giao dịch thành công!",
                                    'action' => 'delete_transaction',
                                    'transaction_id' => $transactionId
                                ]);
                            } else {
                                return response()->json([
                                    'success' => false,
                                    'question' => $userQuestion,
                                    'answer' => "Không tìm thấy giao dịch với ID: " . $transactionId . " hoặc giao dịch không thuộc về bạn.",
                                ]);
                            }
                        }
                    }
                }
            }


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
