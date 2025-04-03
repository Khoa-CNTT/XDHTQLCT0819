<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Đăng ký tài khoản",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="name", type="string", example="Nguyen Van A")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Đăng ký thành công"),
     *     @OA\Response(response=400, description="Email đã được sử dụng")
     * )
     */
    public function register(Request $request)
    {
        return response()->json(['message' => 'Đăng ký thành công'], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Đăng nhập người dùng",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đăng nhập thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="your-auth-token")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Email hoặc mật khẩu không đúng")
     * )
     */
    public function login(Request $request)
    {
        return response()->json(['token' => 'your-auth-token'], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Lấy thông tin người dùng hiện tại",
     *     tags={"Auth"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin người dùng",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Nguyen Van A"),
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Chưa xác thực")
     * )
     */
    public function getUser(Request $request)
    {
        return $request->user();
    }
}
