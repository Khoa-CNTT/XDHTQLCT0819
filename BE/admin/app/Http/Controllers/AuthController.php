<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Mail\SendOtpMail;
use App\Mail\VerifyEmailMail;
use App\Models\User;
use App\Traits\UserActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;



class AuthController extends Controller
{
    use UserActivityLogger;

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Đăng ký tài khoản",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"fullName","email","password"},
     *             @OA\Property(property="username", type="string", example="user1"),
     *             @OA\Property(property="email", type="string", format="email", example="a@gmail.com"),
     *             @OA\Property(property="password", type="string", example="123456"),
     *             @OA\Property(property="phone", type="string", example="0912345678"),
     *             @OA\Property(property="address", type="string", example="Hanoi"),
     *             @OA\Property(property="fullName", type="string", example="Nguyen Van A")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đăng ký thành công"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ"
     *     )
     * )
     */

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fullName' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'phone' => ['nullable', 'unique:users,phone', 'regex:/^(03|05|07|08|09)[0-9]{8}$/'],
                'username' => 'required|unique:users,username',
            ], [
                'email.unique' => 'Email đã tồn tại. Vui lòng chọn email khác.',
                'phone.unique' => 'Số điện thoại đã tồn tại. Vui lòng chọn số điện thoại khác.',
                'phone.regex' => 'Số điện thoại không hợp lệ',
                'username.unique' => 'Tên người dùng đã tồn tại. Vui lòng chọn tên người dùng khác.',
                'password.required' => 'Vui lòng nhập mật khẩu.',
                'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors()->all()
                ], 400);
            }

            $user = User::create([
                'username' => $request->username,
                'email' => trim($request->email),
                'password' => Hash::make($request->password),
                'fullName'  => $request->fullName,
                'phone' => $request->phone,
                'monthly_income' => 0,
                'monthly_customer_spending' => 0,
                'currency' => 'VND',
                'isActived' => false,
                'isBlocked' => false,
                'status' => false,
                'address' => $request->address,
                'avatar' => null,
            ]);

            $token = $user->createToken('thisprivate')->plainTextToken;

            $verifyToken = Str::random(60);
            $user->verify_token = $verifyToken;
            $user->save();

            Mail::to($user->email)->send(new VerifyEmailMail($user, $verifyToken));

            return response()->json([
                'message' => 'Đăng ký thành công, vui lòng kiểm tra email để kích hoạt tài khoản.',
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra, vui lòng thử lại.',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    /**
     * @OA\Get(
     *     path="/api/verify-email",
     *     summary="Xác thực email (kích hoạt tài khoản)",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="Mã token xác thực được gửi qua email",
     *         required=true,
     *         @OA\Schema(type="string", example="Abc123xyz456token")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tài khoản đã được kích hoạt thành công.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tài khoản đã được kích hoạt thành công.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Token không hợp lệ hoặc đã hết hạn.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token không hợp lệ hoặc đã hết hạn.")
     *         )
     *     )
     * )
     */



    public function verifyEmail(Request $request)
    {
        $token = $request->query('token');
        $user = User::where('verify_token', $token)->first();

        $redirectUrl = env('FRONTEND_LOGIN_URL', 'http://localhost:8080/login');

        if (!$user) {
            $url = $redirectUrl . '?status=error&message=' . urlencode('Token không hợp lệ hoặc đã hết hạn.');
            return redirect($url);
        }

        $user->isActived = true;
        $user->verify_token = null;
        $user->save();

        $url = $redirectUrl . '?status=success&message=' . urlencode('Tài khoản đã được kích hoạt thành công.');
        return redirect($url);
    }


    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Đăng nhập người dùng",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đăng nhập thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Không có quyền")
     * )
     */

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'Tài khoản không tồn tại'], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Mật khẩu không chính xác'], 401);
        }

        if (!$user->isActived) {
            return response()->json(['error' => 'Tài khoản chưa được kích hoạt'], 401);
        }

        if ($user->isBlocked) {
            return response()->json(['error' => 'Tài khoản bị khóa'], 401);
        }
        $user->status = true;
        $user->last_login = Carbon::now();
        $user->save();

        $token = $user->createToken('API Token')->plainTextToken;
        $this->logAction('Đăng nhập thành công', $user->id);

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'token' => $token,
            'user' => $user
        ]);
    }


    public function logout(Request $request)
    {
        $user = $request->user();

        $user->status = false;
        $user->save();

        $user->currentAccessToken()->delete();

        $this->logAction('Đăng xuất thành công');

        return response()->json([
            'message' => 'Đăng xuất thành công'
        ]);
    }


    public function resetShow(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');
        return view('comfirm-password.view-reset-password', compact('token', 'email'));
    }



    public function changePassword(Request $request)
    {
        $messages = [
            'current_password.required' => 'Mật khẩu cũ không được để trống.',
            'new_password.required' => 'Mật khẩu mới không được để trống.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'new_password_confirmation.required' => 'Mật khẩu xác nhận không được để trống.',
        ];

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
        ], $messages);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'error' => 'Mật khẩu hiện tại không chính xác!',
            ], 400);
        }

        if ($request->new_password !== $request->new_password_confirmation) {
            return response()->json([
                'error' => 'Mật khẩu xác nhận không khớp!',
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();
        $this->logAction('Đã đổi mật khẩu thành công');
        return response()->json([
            'message' => 'Đã đổi mật khẩu thành công!',
        ]);
    }



    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.exists' => 'Địa chỉ email không tồn tại trong hệ thống.',
        ]);

        $email = $request->email;
        $existing = DB::table('password_reset_tokens')->where('email', $email)->first();

        if ($existing && $existing->locked_until && Carbon::parse($existing->locked_until)->isFuture()) {
            $lockedUntil = Carbon::parse($existing->locked_until)->diffForHumans();
            return response()->json([
                'message' => "Tài khoản đang bị khóa do gửi quá nhiều yêu cầu. Vui lòng thử lại sau $lockedUntil.",
                'locked_until' => $lockedUntil,
                'send_attempts' => $existing->send_attempts,
            ], 403);
        }

        // Reset nếu đã qua thời gian khóa
        if ($existing && $existing->locked_until && Carbon::parse($existing->locked_until)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->update([
                'send_attempts' => 1,
                'locked_until' => null,
            ]);
            $existing = DB::table('password_reset_tokens')->where('email', $email)->first();
        }

        // Nếu đã gửi >= 5 lần thì khóa tài khoản
        if ($existing && $existing->send_attempts >= 5) {
            DB::table('password_reset_tokens')->where('email', $email)->update([
                'locked_until' => Carbon::now()->addDay(),
                'send_attempts' => 5,
            ]);

            return response()->json([
                'message' => 'Bạn đã gửi yêu cầu quá 5 lần. Tài khoản đã bị khóa trong 24 giờ.',
                'locked_until' => Carbon::now()->addDay()->toDateTimeString(),
            ], 403);
        }

        $otp = rand(100000, 999999);

        if ($existing) {
            DB::table('password_reset_tokens')->where('email', $email)->update([
                'token' => $otp,
                'created_at' => Carbon::now(),
                'send_attempts' => $existing->send_attempts + 1,
                'failed_attempts' => 0,
                'locked_until' => null,
            ]);
        } else {
            DB::table('password_reset_tokens')->insert([
                'email' => $email,
                'token' => $otp,
                'created_at' => Carbon::now(),
                'send_attempts' => 1,
                'failed_attempts' => 0,
                'locked_until' => null,
            ]);
        }

        try {
            Mail::to($email)->send(new SendOtpMail($otp));
            return response()->json([
                'message' => 'Mã xác thực (OTP) đã được gửi đến email của bạn.',
                'send_attempts' => $existing ? $existing->send_attempts + 1 : 1,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không thể gửi mã xác thực. Chi tiết: ' . $e->getMessage(),
            ], 500);
        }
    }





    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.exists' => 'Địa chỉ email không tồn tại trong hệ thống.',
            'otp.required' => 'Vui lòng nhập mã OTP.',
            'otp.digits' => 'Mã OTP phải gồm đúng 6 chữ số.',
        ]);

        $email = $request->email;
        $otp = $request->otp;

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            return response()->json(['message' => 'Không tìm thấy yêu cầu đặt lại mật khẩu.'], 404);
        }

        if ($record->locked_until && Carbon::parse($record->locked_until)->isFuture()) {
            $lockedUntil = Carbon::parse($record->locked_until)->diffForHumans();
            return response()->json([
                'message' => "Bạn đã nhập sai OTP quá 5 lần. Vui lòng thử lại sau $lockedUntil.",
            ], 403);
        }

        if ($record->token != $otp) {
            $failedAttempts = ($record->failed_attempts ?? 0) + 1;
            $maxAttempts = 5;
            $attemptsLeft = max(0, $maxAttempts - $failedAttempts);

            $updateData = ['failed_attempts' => $failedAttempts];
            $locked = false;
            $lockedUntilText = null;

            if ($failedAttempts >= $maxAttempts) {
                $lockedUntil = Carbon::now()->addMinutes(15);
                $updateData['locked_until'] = $lockedUntil;
                $locked = true;
                $lockedUntilText = $lockedUntil->diffForHumans();
            }

            DB::table('password_reset_tokens')->where('email', $email)->update($updateData);

            $message = 'Mã OTP không đúng.';
            if (!$locked && $attemptsLeft > 0) {
                $message .= " Bạn còn $attemptsLeft lần thử nữa.";
            } elseif ($locked) {
                $message .= " Bạn đã nhập sai quá $maxAttempts lần. Tài khoản bị khóa trong 15 phút.";
            }

            return response()->json([
                'message' => $message,
                'attempts_left' => $attemptsLeft,
                'locked' => $locked,
                'locked_until' => $lockedUntilText,
            ], 400);
        }

        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            return response()->json(['message' => 'Mã OTP đã hết hạn. Vui lòng yêu cầu mã mới.'], 400);
        }

        DB::table('password_reset_tokens')->where('email', $email)->update([
            'failed_attempts' => 0,
            'locked_until' => null,
        ]);

        return response()->json(['message' => 'Xác thực OTP thành công.']);
    }



    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $updatePassword = DB::table('password_reset_tokens')
            ->where([
                'email' => $request->email,
                'token' => $request->token
            ])
            ->first();

        if (!$updatePassword) {
            return response()->json(['error' => 'Không có token'], 400);
        }

        $tokenCreatedAt = Carbon::parse($updatePassword->created_at);
        if (Carbon::now()->diffInMinutes($tokenCreatedAt) > 60) {
            return response()->json(['error' => 'Token đã hết hạn'], 400);
        }

        User::where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        $this->logAction('Mật khẩu đã được đặt lại thành công');

        return response()->json([
            'message' => 'Mật khẩu đã được đặt lại thành công!'
        ]);
    }
}
