<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ZaloPayController extends Controller
{
    private $config;

    public function __construct()
    {
        $this->config = [
            "appid" => 2554,
            "key1" => "sdngKKJmqEMzvh5QQcdD2A9XBSKUNaYn",
            "key2" => "trMrHtvjo6myautxDUiAcYsVtaeQ8nhf",
            "endpoint" => "https://zalopay.com.vn/v001/tpe/createorder"
        ];
    }

    public function payment(Request $request)
    {
        try {
            $amount = 10000;
            $apptransid = Carbon::now()->format("ymd") . "_" . uniqid();

            $order = [
                "appid" => $this->config["appid"],
                "apptransid" => $apptransid,
                "appuser" => "demo",
                "apptime" => round(microtime(true) * 1000),
                "amount" => $amount,
                "description" => "Thanh toán đơn hàng #$apptransid",
                "bankcode" => "zalopayapp",
                "item" => json_encode([]),
                "embeddata" => json_encode([])
            ];

            // Log dữ liệu order trước khi gửi đi
            Log::info('Order Data Before Sending to ZaloPay', ['order' => $order]);

            $data = implode("|", [
                $order["appid"],
                $order["apptransid"],
                $order["appuser"],
                $order["amount"],
                $order["apptime"],
                $order["embeddata"],
                $order["item"]
            ]);

            $order["mac"] = hash_hmac("sha256", $data, $this->config["key1"]);

            // Log dữ liệu MAC và tính toán
            Log::info('Data for MAC Calculation', ['data' => $data]);
            Log::info('Calculated MAC', ['mac' => $order["mac"]]);

            // Gửi yêu cầu thanh toán
            $response = Http::timeout(30)->post($this->config["endpoint"], $order);

            // Log phản hồi từ API
            Log::info('ZaloPay API Response Status', ['status' => $response->status()]);
            Log::info('ZaloPay API Response Body', ['body' => $response->body()]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('ZaloPay API Response Data', ['result' => $result]);

                if (isset($result['return_code']) && $result['return_code'] == 1) {
                    return response()->json($result);
                } else {
                    Log::error('ZaloPay Error Response', ['error_message' => $result['return_message'] ?? 'Unknown error']);
                    return response()->json([
                        'error' => $result['return_message'] ?? 'Unable to process the payment'
                    ], 400);
                }
            } else {
                // Log lỗi nếu phản hồi không thành công
                Log::error('ZaloPay API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'request_data' => $order
                ]);
                return response()->json(['error' => 'Unable to process the payment. Please try again later.'], 500);
            }
        } catch (\Exception $e) {
            // Log lỗi ngoại lệ
            Log::error('ZaloPay Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'An error occurred. Please try again later.'], 500);
        }
    }



    public function getBanks(Request $request)
    {
        try {
            $endpoint = "https://sbgateway.zalopay.vn/api/getlistmerchantbanks";

            $data = [
                "appid" => $this->config["appid"],
                "reqtime" => round(microtime(true) * 1000)
            ];

            $data["mac"] = hash_hmac("sha256", $data["appid"] . "|" . $data["reqtime"], $this->config["key1"]);

            $response = Http::timeout(30)->get($endpoint, $data);

            if ($response->successful()) {
                $result = $response->json();
                return response()->json($result);
            } else {
                Log::error('Failed to get banks', ['response' => $response->body()]);
                return response()->json(['error' => 'Unable to retrieve bank list'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Exception when getting banks', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        try {
            $dataJson = $request->getContent();
            $data = json_decode($dataJson, true);

            Log::info('ZaloPay Callback', ['data' => $data]);

            // Verify callback data
            if (!isset($data['data']) || !isset($data['mac'])) {
                return response()->json(['return_code' => -1, 'return_message' => 'Invalid data']);
            }

            // Check MAC
            $mac = hash_hmac("sha256", $data['data'], $this->config["key2"]);

            if ($mac !== $data['mac']) {
                Log::error('ZaloPay Callback MAC Invalid', [
                    'received_mac' => $data['mac'],
                    'calculated_mac' => $mac
                ]);
                return response()->json(['return_code' => -1, 'return_message' => 'MAC not match']);
            }

            // Process callback data
            $callbackData = json_decode($data['data'], true);

            // TODO: Update your order status based on callback data
            // Example: Order::where('transaction_id', $callbackData['apptransid'])->update(['status' => 'paid']);

            Log::info('ZaloPay Callback Processed', ['callback_data' => $callbackData]);

            return response()->json([
                'return_code' => 1,
                'return_message' => 'success'
            ]);
        } catch (\Exception $e) {
            Log::error('ZaloPay Callback Exception', ['message' => $e->getMessage()]);
            return response()->json([
                'return_code' => -1,
                'return_message' => 'Failed to process callback'
            ], 500);
        }
    }

    public function getTransactionHistory(Request $request)
    {
        try {
            $endpoint = "https://sbgateway.zalopay.vn/v2/query";

            $apptransid = $request->input('apptransid');
            if (!$apptransid) {
                return response()->json(['error' => 'Transaction ID is required'], 400);
            }

            $data = [
                "appid" => $this->config["appid"],
                "apptransid" => $apptransid,
                "reqtime" => round(microtime(true) * 1000),
            ];

            $data["mac"] = hash_hmac(
                "sha256",
                $data["appid"] . "|" . $data["apptransid"] . "|" . $data["reqtime"],
                $this->config["key1"]
            );

            $response = Http::timeout(30)->post($endpoint, $data);

            if ($response->successful()) {
                $result = $response->json();
                return response()->json($result);
            } else {
                Log::error('Failed to get transaction history', ['response' => $response->body()]);
                return response()->json(['error' => 'Unable to retrieve transaction history'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Exception when getting transaction history', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}
