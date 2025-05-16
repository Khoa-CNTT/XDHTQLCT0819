<?php

namespace App\Http\Controllers;

use App\Models\Savingoal;
use App\Http\Controllers\Controller;
use App\Mail\GoalCompletedMail;
use App\Models\User;
use App\Traits\UserActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class SavingoalController extends Controller
{
    use UserActivityLogger;

    public function index()
    {
        try {
            $user = auth()->user();

            $goals = Savingoal::where('user_id', $user->id)->get()->map(function ($goal) use ($user) {
                $start = Carbon::parse($goal->start_day);
                $end = Carbon::parse($goal->end_day);
                $now = Carbon::now();

                $goal->duration_days = $start->diffInDays($end);
                $goal->remaining_days = $now->diffInDays($end, false);
                $goal->savings_percentage = round(($goal->save_money / $goal->target) * 100, 2);
                if ($goal->savings_percentage >= 100 && !$goal->is_completed) {
                    Mail::to($user->email)->send(new GoalCompletedMail($goal));

                    Savingoal::where('id', $goal->id)->update(['is_completed' => true]);
                }

                return $goal;
            });

            return response()->json([
                'success' => true,
                'data' => $goals
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải mục tiêu: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Không thể lấy mục tiêu tại thời điểm này. Vui lòng thử lại sau'
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'       => 'required|string|max:255',
                'target'     => 'required|numeric|min:0',
                'save_money' => 'required|numeric|min:0',
                'start_day'  => 'required|date',
                'end_day'    => 'required|date|after_or_equal:start_day',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            $data['user_id'] = auth()->id();

            $slug = Str::slug($data['name']);
            if (Savingoal::where('slug', $slug)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tên mục tiêu đã tồn tại. Vui lòng chọn tên khác.'
                ], 409);
            }
            $data['slug'] = $slug;

            $data['savings_percentage'] = $data['target'] > 0
                ? round(($data['save_money'] / $data['target']) * 100, 2)
                : 0;

            $goal = Savingoal::create($data);
            $this->logAction('Đã thêm mục tiêu: ' . $goal->name . ' thành công');

            return response()->json([
                'success' => true,
                'message' => 'Tạo mục tiêu tiết kiệm thành công.',
                'data' => $goal
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tạo mục tiêu: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo mục tiêu vào lúc này.',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    public function edit($id)
    {
        try {
            $goal = Savingoal::where('user_id', auth()->id())->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $goal
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải mục tiêu: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Không thể tìm thấy mục tiêu.',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $goal = Savingoal::where('user_id', auth()->id())->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name'                => 'required|string|max:255',
                'target'              => 'required|numeric|min:0',
                'save_money'          => 'required|numeric|min:0',
                'start_day'           => 'required|date',
                'end_day'             => 'required|date|after_or_equal:start_day',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            if ($request->has('name') && $request->name !== $goal->name) {
                $slug = Str::slug($request->name);
                $slugExists = Savingoal::where('slug', $slug)->where('id', '!=', $goal->id)->exists();
                if ($slugExists) {
                    return response()->json(['message' => 'Mục tiêu đã tồn tại. Vui lòng chọn Mục tiêu khác.'], 422);
                }

                $data['slug'] = $slug;
            }

            $goal->update($data);
            $this->logAction('Đã cập nhật mục tiêu: ' . $goal->name . ' thành công');

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật mục tiêu thành công.',
                'data' => $goal
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật mục tiêu: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật mục tiêu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $goal = Savingoal::where('user_id', auth()->id())->findOrFail($id);
            $goal->delete();
            $this->logAction('Đã xoá mục tiêu: ' . $goal->name . ' thành công');

            return response()->json([
                'success' => true,
                'message' => 'Xóa mục tiêu thành công.'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi xóa mục tiêu: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa mục tiêu.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function updateSaveMoney(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số tiền không hợp lệ.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::find(auth()->id());

            if ($user->monthly_customer_spending < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số dư không đủ để thêm vào mục tiêu.'
                ], 400);
            }

            $goal = Savingoal::where('user_id', $user->id)->findOrFail($id);

            if ($goal->is_completed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mục tiêu đã hoàn thành. Không thể thêm tiền nữa.'
                ], 400);
            }

            $user->monthly_customer_spending -= $request->amount;
            $user->save();

            $goal->save_money += $request->amount;
            $goal->savings_percentage = round(($goal->save_money / $goal->target) * 100, 2);
            $goal->save();
            $this->logAction('Đã Thêm tiền tiết kiệm cho mục tiêu: ' . $goal->name . ' thành công');

            return response()->json([
                'success' => true,
                'message' => 'Thêm tiền tiết kiệm thành công!',
                'data' => [
                    'goal' => $goal,
                    'monthly_customer_spending' => $user->monthly_customer_spending
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi cập nhật tiết kiệm: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật số tiền vào lúc này.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
