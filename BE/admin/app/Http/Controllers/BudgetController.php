<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BudgetController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'budget_limit' => 'required|numeric',
            'warning_threshold' => 'required|numeric',
        ], [
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'category_id.exists' => 'Danh mục đã chọn không hợp lệ.',
            'budget_limit.required' => 'Vui lòng nhập hạn mức ngân sách.',
            'budget_limit.numeric' => 'Hạn mức ngân sách phải là số.',
            'warning_threshold.required' => 'Vui lòng nhập ngưỡng cảnh báo.',
            'warning_threshold.numeric' => 'Ngưỡng cảnh báo phải là số.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->warning_threshold > $request->budget_limit) {
            return response()->json([
                'message' => 'Ngưỡng cảnh báo không được lớn hơn hạn mức ngân sách.'
            ], 422);
        }

        $userId = Auth::id();
        $exists = Budget::where('user_id', $userId)
            ->where('category_id', $request->category_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Danh mục này đã có ngân sách. Vui lòng chọn danh mục khác.'
            ], 409);
        }

        $budget = Budget::create([
            'user_id' => $userId,
            'category_id' => $request->category_id,
            'budget_limit' => $request->budget_limit,
            'warning_threshold' => $request->warning_threshold,
        ]);

        return response()->json([
            'message' => 'Ngân sách đã được tạo thành công',
            'budget' => $budget,
        ]);
    }


    public function getBudgetSummary()
    {
        $userId = auth()->id();

        $spentSubQuery = DB::table('transactions')
            ->select('category_id', DB::raw('SUM(amount) as total_spent'))
            ->where('user_id', $userId)
            ->where('transaction_type', 'expense')
            ->groupBy('category_id');

        $budgets = DB::table('budgets')
            ->join('categories', 'budgets.category_id', '=', 'categories.id')
            ->leftJoinSub($spentSubQuery, 'spent', function ($join) {
                $join->on('budgets.category_id', '=', 'spent.category_id');
            })
            ->where('budgets.user_id', $userId)
            ->select(
                'budgets.id',
                'categories.name as category_name',
                'categories.icon',
                'budgets.budget_limit',
                'budgets.warning_threshold',
                DB::raw('COALESCE(spent.total_spent, 0) as total_spent')
            )
            ->get();

        $totalBudget = $budgets->sum('budget_limit');
        $totalSpent = $budgets->sum('total_spent');
        $totalSpentPercentageRaw = $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : 0;

        $totalSpentPercentage = $totalSpent > $totalBudget
            ? -round($totalSpentPercentageRaw - 100, 2)
            : round($totalSpentPercentageRaw, 2);

        $detailed = $budgets->map(function ($b) {
            $spent = $b->total_spent ?? 0;
            $percentRaw = $b->budget_limit > 0 ? ($spent / $b->budget_limit) * 100 : 0;

            $percent = $spent > $b->budget_limit
                ? -round($percentRaw - 100, 2)
                : round($percentRaw, 2);

            $status = 'Bình thường';
            if ($spent >= $b->budget_limit) {
                $status = 'Vượt ngưỡng';
            } elseif ($spent >= $b->warning_threshold) {
                $status = 'Gần ngưỡng';
            }

            return [
                'id' => $b->id,
                'category_name' => $b->category_name,
                'icon' => $b->icon,
                'budget_limit' => number_format($b->budget_limit, 0, ',', '.') . ' ₫',
                'spent' => number_format($spent, 0, ',', '.') . ' ₫',
                'warning_threshold' => number_format($b->warning_threshold, 0, ',', '.') . ' ₫',
                'status' => $status,
                'percent' => $percent
            ];
        });

        $topOverThreshold = $detailed->sortByDesc('percent')->take(2)->values();

        return response()->json([
            'totalBudget' => number_format($totalBudget, 0, ',', '.') . ' ₫',
            'totalSpent' => number_format($totalSpent, 0, ',', '.') . ' ₫',
            'totalSpentPercentage' => $totalSpentPercentage,
            'topOverThreshold' => $topOverThreshold,
            'detailed' => $detailed
        ]);
    }



    public function getBudgetAlerts()
    {
        $userId = auth()->id();

        $budgets = DB::table('budgets')
            ->join('categories', 'budgets.category_id', '=', 'categories.id')
            ->leftJoin('transactions', function ($join) use ($userId) {
                $join->on('budgets.category_id', '=', 'transactions.category_id')
                    ->where('transactions.user_id', '=', $userId)
                    ->where('transactions.transaction_type', '=', 'expense');
            })
            ->where('budgets.user_id', $userId)
            ->select(
                'categories.name as category_name',
                'budgets.budget_limit',
                'budgets.warning_threshold',
                DB::raw('SUM(transactions.amount) as total_spent')
            )
            ->groupBy(
                'budgets.id',
                'categories.name',
                'budgets.budget_limit',
                'budgets.warning_threshold'
            )
            ->get();

        $alerts = [];

        foreach ($budgets as $budget) {
            $spent = $budget->total_spent ?? 0;

            if ($spent >= $budget->budget_limit) {
                $alerts[] = "Ngân sách danh mục <strong>{$budget->category_name}</strong> đã vượt ngưỡng. Vui lòng điều chỉnh chi tiêu!";
            } elseif ($spent >= $budget->warning_threshold) {
                $alerts[] = "Ngân sách danh mục <strong>{$budget->category_name}</strong> sắp vượt ngưỡng. Hãy cẩn thận!";
            }
        }

        return response()->json([
            'alerts' => $alerts
        ], 200);
    }

    public function getCategoryBudgetStatus($categoryId)
    {
        $userId = auth()->id();

        $budget = DB::table('budgets')
            ->join('categories', 'budgets.category_id', '=', 'categories.id')
            ->leftJoin('transactions', function ($join) use ($userId) {
                $join->on('budgets.category_id', '=', 'transactions.category_id')
                    ->where('transactions.user_id', '=', $userId)
                    ->where('transactions.transaction_type', '=', 'expense');
            })
            ->where('budgets.user_id', $userId)
            ->where('budgets.category_id', $categoryId)
            ->select(
                'categories.name as category_name',
                'budgets.budget_limit',
                'budgets.warning_threshold',
                DB::raw('SUM(transactions.amount) as total_spent')
            )
            ->groupBy(
                'budgets.id',
                'categories.name',
                'budgets.budget_limit',
                'budgets.warning_threshold'
            )
            ->first();

        if (!$budget) {
            return response()->json([
                'error' => 'Không tìm thấy danh mục hoặc không có ngân sách được chỉ định cho danh mục này'
            ], 404);
        }

        $spent = $budget->total_spent ?? 0;
        $status = '';

        if ($spent >= $budget->budget_limit) {
            $status = "Ngân sách danh mục <strong>{$budget->category_name}</strong> đã vượt ngưỡng.Vui lòng điều chỉnh chi tiêu!";
        } elseif ($spent >= $budget->warning_threshold) {
            $status = "Ngân sách danh mục <strong>{$budget->category_name}</strong> sắp vượt ngưỡng.Hãy cẩn thận!";
        }

        return response()->json([
            'status' => $status
        ], 200);
    }


    public function edit($id)
    {
        $budget = Budget::where('user_id', Auth::id())->find($id);

        if (!$budget) {
            return response()->json(['message' => 'Ngân sách không tồn tại'], 404);
        }

        return response()->json(['budget' => $budget]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'budget_limit' => 'required|numeric',
            'warning_threshold' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->warning_threshold > $request->budget_limit) {
            return response()->json([
                'message' => 'Ngưỡng cảnh báo không được lớn hơn hạn mức ngân sách.'
            ], 422);
        }

        $userId = Auth::id();
        $budget = Budget::where('user_id', $userId)->find($id);

        if (!$budget) {
            return response()->json(['message' => 'Ngân sách không tồn tại'], 404);
        }

        $exists = Budget::where('user_id', $userId)
            ->where('category_id', $request->category_id)
            ->where('id', '<>', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Danh mục này đã có ngân sách. Vui lòng chọn danh mục khác.'
            ], 409);
        }

        $budget->update([
            'category_id' => $request->category_id,
            'budget_limit' => $request->budget_limit,
            'warning_threshold' => $request->warning_threshold,
        ]);

        return response()->json([
            'message' => 'Ngân sách đã được cập nhật thành công',
            'budget' => $budget,
        ]);
    }



    public function destroy($id)
    {
        $budget = Budget::where('user_id', Auth::id())->find($id);

        if (!$budget) {
            return response()->json(['message' => 'Ngân sách không tồn tại'], 404);
        }

        $budget->delete();

        return response()->json(['message' => 'Ngân sách đã được xoá thành công']);
    }
}
