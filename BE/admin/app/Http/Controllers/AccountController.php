<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/accounts",
     *     summary="Lấy danh sách tài khoản của người dùng hiện tại",
     *     tags={"Account"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách tài khoản",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Tài khoản chính"),
     *                 @OA\Property(property="type", type="string", example="mbank"),
     *                 @OA\Property(property="number_card", type="integer", example=123456789),
     *                 @OA\Property(property="expired", type="string", format="date", example="2030-12-31"),
     *                 @OA\Property(property="password", type="string", example=1234)
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $userId = Auth::id();

        $accounts = Account::with('user')
            ->where('user_id', $userId)
            ->get();

        return response()->json($accounts);
    }

    /**
     * @OA\Post(
     *     path="/api/accounts",
     *     summary="Tạo tài khoản mới",
     *     tags={"Account"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "type", "number_card", "expired", "password"},
     *             @OA\Property(property="name", type="string", example="Tài khoản chính"),
     *             @OA\Property(property="type", type="string", example="mbank"),
     *             @OA\Property(property="number_card", type="integer", example=123456789),
     *             @OA\Property(property="expired", type="string", format="date", example="2030-12-31"),
     *             @OA\Property(property="password", type="string", example=1234)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tạo tài khoản thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tạo tài khoản thành công"),
     *             @OA\Property(property="account", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Tài khoản chính"),
     *                 @OA\Property(property="type", type="string", example="mbank"),
     *                 @OA\Property(property="number_card", type="integer", example=123456789),
     *                 @OA\Property(property="expired", type="string", format="date", example="2030-12-31"),
     *                 @OA\Property(property="password", type="string", example=1234)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:accounts,name|max:255',
            'type' => 'required|in:mbank',
            'number_card' => 'required|string',
            'expired' => 'required|date|unique:accounts,expired',
            'password' => 'required|string',
        ]);

        $check = Account::where('number_card', $request->number_card)
            ->where('type', $request->type)
            ->first();

        if ($check) {
            return response()->json(['message' => 'Tài khoản đã tồn tại'], 422);
        }

        if ($validator->fails()) {
            Log::info(['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['user_id'] = Auth::id();
        $data['password'] = bcrypt($request->password);

        $account = Account::create($data);

        return response()->json(['message' => 'Tạo tài khoản thành công', 'account' => $account]);
    }

    /**
     * @OA\Get(
     *     path="/api/accounts/{id}",
     *     summary="Xem chi tiết một tài khoản",
     *     tags={"Account"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của tài khoản",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin tài khoản",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Tài khoản chính"),
     *             @OA\Property(property="type", type="string", example="mbank"),
     *             @OA\Property(property="number_card", type="integer", example=123456789),
     *             @OA\Property(property="expired", type="string", format="date", example="2030-12-31"),
     *             @OA\Property(property="password", type="string", example=1234)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy tài khoản"
     *     )
     * )
     */
    public function edit($id)
    {
        $userId = Auth::id();
        $account = Account::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$account) {
            return response()->json(['message' => 'Không tìm thấy tài khoản'], 404);
        }

        return response()->json($account);
    }

    /**
     * @OA\Put(
     *     path="/api/accounts/{id}",
     *     summary="Cập nhật thông tin tài khoản",
     *     tags={"Account"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của tài khoản",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Tài khoản chính sửa đổi"),
     *             @OA\Property(property="type", type="string", example="mbank"),
     *             @OA\Property(property="number_card", type="integer", example=987654321),
     *             @OA\Property(property="expired", type="string", format="date", example="2035-01-01"),
     *             @OA\Property(property="password", type="string", example=4321)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật tài khoản thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cập nhật tài khoản thành công"),
     *             @OA\Property(property="account", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Tài khoản chính sửa đổi"),
     *                 @OA\Property(property="type", type="string", example="mbank"),
     *                 @OA\Property(property="number_card", type="integer", example=987654321),
     *                 @OA\Property(property="expired", type="string", format="date", example="2035-01-01"),
     *                 @OA\Property(property="password", type="string", example=4321)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy tài khoản"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        $account = Account::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$account) {
            return response()->json(['message' => 'Không tìm thấy tài khoản'], 404);
        }

        $request->validate([
            'name'          => 'sometimes|string|unique:accounts,name,' . $account->id,
            'type'          => 'sometimes|in:mbank',
            'number_card'   => 'sometimes|integer|unique:accounts,number_card,' . $account->id,
            'expired'       => 'sometimes|date|unique:accounts,expired,' . $account->id,
            'password'      => 'sometimes|integer|unique:accounts,password,' . $account->id,
        ]);

        $account->update($request->only(['name', 'type', 'number_card', 'expired', 'password']));

        return response()->json(['message' => 'Cập nhật tài khoản thành công', 'account' => $account]);
    }

    /**
     * @OA\Delete(
     *     path="/api/accounts/{id}",
     *     summary="Xóa tài khoản",
     *     tags={"Account"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của tài khoản",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa tài khoản thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Xóa tài khoản thành công")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy tài khoản"
     *     )
     * )
     */
    public function destroy($id)
    {
        $userId = Auth::id();
        $account = Account::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$account) {
            return response()->json(['message' => 'Không tìm thấy tài khoản'], 404);
        }

        $account->delete();
        return response()->json(['message' => 'Xóa tài khoản thành công']);
    }

    /**
     * @OA\Put(
     *     path="/api/account/set-primary-account/{id}",
     *     summary="Thiết lập tài khoản chính",
     *     tags={"Account"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID của tài khoản cần thiết lập làm tài khoản chính",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tài khoản đã được thiết lập thành tài khoản chính",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tài khoản đã được thiết lập thành tài khoản chính")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Yêu cầu không hợp lệ",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Không tìm thấy tài khoản")
     *         )
     *     )
     * )
     */


    public function setPrimaryAccount($id)
    {
        try {
            Account::where('user_id', auth()->user()->id)->update(['is_primary' => false]);

            $account = Account::where('id', $id)->update(['is_primary' => true]);

            if ($account === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản không tồn tại hoặc không thể cập nhật.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tài khoản chính đã được cập nhật thành công.',
                'data' => Account::where('id', $id)->first()
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật tài khoản chính: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại sau.'
            ], 500);
        }
    }
}
