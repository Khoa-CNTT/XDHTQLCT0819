<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Traits\UserActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    use UserActivityLogger;
    public function index()
    {
        $userId = Auth::id();

        $accounts = Account::with('user')
            ->where('user_id', $userId)
            ->get();

        return response()->json($accounts);
    }

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
        $data['password'] = Crypt::encryptString($request->password);

        $account = Account::create($data);

        $this->logAction('Tạo tài khoản mới');
        return response()->json(['message' => 'Tạo tài khoản thành công', 'account' => $account]);
    }


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
            'number_card'   => 'sometimes|string|unique:accounts,number_card,' . $account->id,
            'expired'       => 'sometimes|date|unique:accounts,expired,' . $account->id,
            'password'      => 'sometimes|string|unique:accounts,password,' . $account->id,
        ]);

        $account->update($request->only(['name', 'type', 'number_card', 'expired', 'password']));
        $this->logAction('Cập nhật tài khoản: ' . $account->name . 'thành công');

        return response()->json(['message' => 'Cập nhật tài khoản thành công', 'account' => $account]);
    }


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
        $this->logAction('Đã xoá tài khoản: ' . $account->name . 'thành công');
        return response()->json(['message' => 'Xóa tài khoản thành công']);
    }

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
            $this->logAction('Đặt tài khoản :' . $account->name . ' làm tài khoản chính');
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
