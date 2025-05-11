<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                'khám bệnh'
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
}
