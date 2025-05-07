<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{

    public function index()
    {
        return response()->json(User::all());
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'username' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:15',
            'fullName' => 'string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        User::where('id', $user->id)->update($request->only(['username', 'email', 'phone', 'fullName', 'address']));

        $user = User::find($user->id);

        return response()->json(['message' => 'Cập nhật hồ sơ thành công', 'user' => $user]);
    }

    public function updateAvatar(Request $request)
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

        return response()->json(['message' => 'Cập nhật ảnh đại diện thành công', 'avatar' => $path]);
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



    public function edit($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy người dùng'], 404);
        }

        return response()->json($user);
    }
}
