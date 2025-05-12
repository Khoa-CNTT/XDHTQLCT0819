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

        $budget = Budget::create([
            'user_id' => Auth::id(),
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
                'categories.icon',
                'budgets.budget_limit',
                'budgets.warning_threshold',
                DB::raw('SUM(transactions.amount) as total_spent')
            )
            ->groupBy(
                'budgets.id',
                'categories.name',
                'categories.icon',
                'budgets.budget_limit',
                'budgets.warning_threshold'
            )
            ->get();

        $totalBudget = $budgets->sum('budget_limit');
        $totalSpent = $budgets->sum(function ($item) {
            return $item->total_spent ?? 0;
        });

        $totalSpentPercentage = $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : 0;

        $detailed = $budgets->map(function ($b) {
            $spent = $b->total_spent ?? 0;
            $percent = $b->budget_limit > 0 ? ($spent / $b->budget_limit) * 100 : 0;

            $status = 'Bình thường';
            if ($spent >= $b->budget_limit) {
                $status = 'Vượt ngưỡng';
            } elseif ($spent >= $b->warning_threshold) {
                $status = 'Gần ngưỡng';
            }

            return [
                'category_name' => $b->category_name,
                'icon' => $b->icon,
                'budget_limit' => number_format($b->budget_limit, 0, ',', '.') . ' ₫',
                'spent' => number_format($spent, 0, ',', '.') . ' ₫',
                'warning_threshold' => number_format($b->warning_threshold, 0, ',', '.') . ' ₫',
                'status' => $status,
                'percent' => round($percent, 2)
            ];
        });

        $topOverThreshold = $detailed->sortByDesc('phan_tram')->take(2)->values();

        return response()->json([
            'totalBudget' => number_format($totalBudget, 0, ',', '.') . ' ₫',
            'totalSpent' => number_format($totalSpent, 0, ',', '.') . ' ₫',
            'totalSpentPercentage' => round($totalSpentPercentage, 2),
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
            'canh_bao' => $alerts
        ]);
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

        $budget = Budget::where('user_id', Auth::id())->find($id);

        if (!$budget) {
            return response()->json(['message' => 'Ngân sách không tồn tại'], 404);
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
