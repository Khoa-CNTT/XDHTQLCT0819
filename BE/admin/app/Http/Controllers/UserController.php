<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{

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
            $rules['phone'] = 'nullable|string|max:15|unique:users,phone,' . $user->id;
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
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại.',
            'phone.string' => 'Số điện thoại phải là chuỗi.',
            'phone.max' => 'Số điện thoại không được vượt quá 15 ký tự.',
            'phone.unique' => 'Số điện thoại đã tồn tại.',
            'fullName.string' => 'Họ tên phải là chuỗi.',
            'fullName.max' => 'Họ tên không được vượt quá 255 ký tự.',
            'address.string' => 'Địa chỉ phải là chuỗi.',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự.',
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
}
