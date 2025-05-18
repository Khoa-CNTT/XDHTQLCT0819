<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\UserActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    use UserActivityLogger;

    public function index()
    {
        return response()->json(User::where('role', 'user')->paginate(10));
    }
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $rules = [];

        if ($request->has('username')) {
            $rules['username'] = 'string|max:255|unique:users,username,' . $user->id;
        }

        if ($request->has('phone')) {
            $rules['phone'] = [
                'nullable',
                'string',
                'max:15',
                'unique:users,phone,' . $user->id,
                'regex:/^(03|05|07|08|09)[0-9]{8}$/'
            ];
        }

        if ($request->has('fullName')) {
            $rules['fullName'] = 'string|max:255';
        }

        if ($request->has('address')) {
            $rules['address'] = 'nullable|string|max:255';
        }

        $messages = [
            'username.string' => 'Tên người dùng phải là chuỗi.',
            'username.max' => 'Tên người dùng không được vượt quá 255 ký tự.',
            'username.unique' => 'Tên người dùng đã tồn tại.',
            'phone.string' => 'Số điện thoại phải là chuỗi.',
            'phone.max' => 'Số điện thoại không được vượt quá 15 ký tự.',
            'phone.unique' => 'Số điện thoại đã tồn tại.',
            'phone.regex' => 'Số điện thoại không đúng định dạng',
            'fullName.string' => 'Họ tên phải là chuỗi.',
            'fullName.max' => 'Họ tên không được vượt quá 255 ký tự.',
            'address.string' => 'Địa chỉ phải là chuỗi.',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
        ];

        $validatedData = $request->validate($rules, $messages);

        User::where('id', $user->id)->update($validatedData);

        $user = User::find($user->id);
        $this->logAction('Đã cập nhật thông tin cá nhân thành công');

        return response()->json([
            'message' => 'Cập nhật hồ sơ thành công',
            'user' => $user
        ]);
    }

    public function updateAvatarProfile(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();

        if ($user->avatar) {
            Storage::delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        User::where('id', $user->id)->update(['avatar' => $path]);
        $this->logAction('Đã cập nhật ảnh đại diện thành công');
        return response()->json(['message' => 'Cập nhật ảnh đại diện thành công', 'avatar' => $path, 'user' => $user]);
    }



    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::find($request->id);

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();

        return response()->json(['message' => 'Cập nhật ảnh đại diện thành công', 'avatar' => $path]);
    }

    public function getImage($filename)
    {
        $path = $filename;

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        $file = $disk->get($path);
        $type = $disk->mimeType($path);

        return response($file, 200)->header('Content-Type', $type);
    }


    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'Xóa người dùng thành công']);
    }



    public function update(Request $request)
    {
        $user = User::find($request->id);

        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại.'], 404);
        }

        $rules = [];

        if ($request->has('username')) {
            $rules['username'] = 'string|max:255|unique:users,username,' . $user->id;
        }


        if ($request->has('phone')) {
            $rules['phone'] = [
                'nullable',
                'string',
                'max:15',
                'regex:/^(0|\+84)(3[2-9]|5[6|8|9]|7[0|6-9]|8[1-5]|9[0-9])[0-9]{7}$/',
                'unique:users,phone,' . $user->id
            ];
        }

        if ($request->has('fullName')) {
            $rules['fullName'] = 'string|max:255';
        }

        if ($request->has('address')) {
            $rules['address'] = 'nullable|string|max:255';
        }
        if ($request->has('role')) {
            $rules['role'] = 'in:admin,user';
        }

        $messages = [
            'username.string' => 'Tên người dùng phải là chuỗi.',
            'username.max' => 'Tên người dùng không được vượt quá 255 ký tự.',
            'username.unique' => 'Tên người dùng đã tồn tại.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại.',
            'phone.string' => 'Số điện thoại phải là chuỗi.',
            'phone.max' => 'Số điện thoại không được vượt quá 15 ký tự.',
            'phone.unique' => 'Số điện thoại đã tồn tại.',
            'fullName.string' => 'Họ tên phải là chuỗi.',
            'fullName.max' => 'Họ tên không được vượt quá 255 ký tự.',
            'phone.regex' => 'Số điện thoại không hợp lệ',
            'address.string' => 'Địa chỉ phải là chuỗi.',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
            'role.in' => 'Vai trò không hợp lệ (chỉ được là admin hoặc user).',
        ];

        $validatedData = $request->validate($rules, $messages);

        $user->update($validatedData);

        return response()->json([
            'message' => 'Cập nhật hồ sơ thành công',
            'user' => $user
        ]);
    }


    public function edit($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy người dùng'], 404);
        }

        return response()->json($user);
    }

    public function updateIncome(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $authUser = auth()->user();

        if (!$authUser) {
            return response()->json(['message' => 'Không xác thực người dùng'], 401);
        }

        $user = User::find($authUser->id);

        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        $user->monthly_income += $request->amount;
        $user->monthly_customer_spending += $request->amount;

        $user->save();

        return response()->json([
            'message' => 'Cập nhật thu nhập thành công',
            'monthly_income' => $user->monthly_income,
            'monthly_customer_spending' => $user->monthly_customer_spending,
        ]);
    }

    public function block($id)
    {
        $user = User::findOrFail($id);

        $user->isBlocked = !$user->isBlocked;
        $user->status = false;

        $user->save();

        $message = $user->isBlocked ? 'Người dùng đã bị khóa.' : 'Người dùng đã được mở khóa.';

        return response()->json(['message' => $message], 200);
    }

    public function profile()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json($user);
    }

    public function getStats()
    {
        $now = Carbon::now();

        $totalUsers = User::where('role', 'user')->count();

        $loginYesterday = User::where('role', 'user')
            ->whereDate('last_login', Carbon::yesterday()->toDateString())
            ->count();

        $loginLastWeek = User::where('role', 'user')
            ->whereBetween('last_login', [
                $now->copy()->subWeek()->startOfWeek(),
                $now->copy()->subWeek()->endOfWeek()
            ])->count();

        $loginLastMonth = User::where('role', 'user')
            ->whereBetween('last_login', [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth()
            ])->count();

        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $now->copy()->endOfWeek(Carbon::SUNDAY);

        $weekLogins = User::where('role', 'user')
            ->whereBetween('last_login', [$weekStart, $weekEnd])
            ->selectRaw('DAYOFWEEK(last_login) as day_of_week, COUNT(*) as count')
            ->groupBy('day_of_week')
            ->get()
            ->keyBy('day_of_week');

        $weekdayMap = [
            1 => 'CN',
            2 => 'T2',
            3 => 'T3',
            4 => 'T4',
            5 => 'T5',
            6 => 'T6',
            7 => 'T7',
        ];

        $weeklyStats = [];
        for ($i = 2; $i <= 8; $i++) {
            $dayIndex = $i == 8 ? 1 : $i;
            $weeklyStats[] = [
                'day' => $weekdayMap[$dayIndex],
                'count' => $weekLogins->has($dayIndex) ? $weekLogins[$dayIndex]->count : 0,
            ];
        }

        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $monthLogins = User::where('role', 'user')
            ->whereBetween('last_login', [$monthStart, $monthEnd])
            ->selectRaw('DAY(last_login) as day, COUNT(*) as count')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $daysInMonth = $now->daysInMonth;
        $monthlyStats = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $monthlyStats[] = [
                'day' => $d,
                'count' => $monthLogins->has($d) ? $monthLogins[$d]->count : 0,
            ];
        }

        $year = $now->year;

        $yearLogins = User::where('role', 'user')
            ->whereYear('last_login', $year)
            ->selectRaw('MONTH(last_login) as month, COUNT(*) as count')
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $yearlyStats = [];
        for ($m = 1; $m <= 12; $m++) {
            $yearlyStats[] = [
                'month' => $m,
                'count' => $yearLogins->has($m) ? $yearLogins[$m]->count : 0,
            ];
        }

        $newUsersThisWeek = User::where('role', 'user')
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();

        $activeUsers = User::where('role', 'user')->where('status', true)->count();
        $inactiveUsers = User::where('role', 'user')->where('status', false)->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_users' => $totalUsers,
                'login_yesterday' => $loginYesterday,
                'login_last_week' => $loginLastWeek,
                'login_last_month' => $loginLastMonth,

                'weekly_stats' => $weeklyStats,
                'monthly_stats' => $monthlyStats,
                'yearly_stats' => $yearlyStats,

                'new_users_this_week' => $newUsersThisWeek,
                'active_users' => $activeUsers,
                'inactive_users' => $inactiveUsers,
            ]
        ]);
    }
}
