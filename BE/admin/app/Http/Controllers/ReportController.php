<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function barChart(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = $user->id;
        $range = $request->query('range', 'month');
        $customRange = $request->input('dateRange');
        [$start, $end, $periods] = $this->getStartEnd($range, $customRange);

        $labels = $this->getLabels($range, $start, $periods);

        $spending = $this->getGroupedData($userId, 'expense', $start, $end, $range);
        $income = $this->getGroupedData($userId, 'income', $start, $end, $range);

        $dataset1 = [];
        $dataset2 = [];

        for ($i = 0; $i < $periods; $i++) {
            $period = $i + 1;
            $dataset1[] = $spending[$period] ?? 0;
            $dataset2[] = $income[$period] ?? 0;
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Chi tiêu', 'data' => $dataset1],
                ['label' => 'Thu nhập', 'data' => $dataset2],
            ]
        ]);
    }

    private function getGroupedData($userId, $type, $start, $end, $range)
    {
        $groupBy = $this->getGroupByClause($range);

        return Transaction::selectRaw("$groupBy as period, sum(amount) as total")
            ->where('user_id', $userId)
            ->where('transaction_type', $type)
            ->whereBetween('transaction_date', [$start, $end])
            ->groupBy('period')
            ->pluck('total', 'period');
    }

    private function getGroupByClause($range)
    {
        switch ($range) {
            case 'week':
                return 'DAYOFWEEK(transaction_date)';
            case 'month':
                return 'WEEK(transaction_date, 3) - WEEK(transaction_date - INTERVAL DAY(transaction_date)-1 DAY, 3) + 1';
            case 'year':
                return 'MONTH(transaction_date)';
            default:
                return 'WEEK(transaction_date, 3) - WEEK(transaction_date - INTERVAL DAY(transaction_date)-1 DAY, 3) + 1';
        }
    }

    private function getLabels($range, $start, $periods)
    {
        switch ($range) {
            case 'week':
                return ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
            case 'month':
                return ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4', 'Tuần 5'];
            case 'year':
                $startDate = Carbon::parse($start)->startOfYear(); 
                $labels = [];
                for ($i = 0; $i < $periods; $i++) {
                    $month = $startDate->copy()->addMonths($i);
                    $labels[] = 'Tháng ' . $month->format('n');
                }
                return $labels;
            default:
                return array_map(function ($i) {
                    return 'Phần ' . ($i + 1);
                }, range(0, $periods - 1));
        }
    }

    public function expensePie(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = $user->id;
        $range = $request->query('range', 'month');
        $customRange = $request->input('dateRange');
        [$start, $end] = $this->getStartEnd($range, $customRange);

        $data = Transaction::selectRaw('category_id, sum(amount) as total')
            ->where('user_id', $userId)
            ->where('transaction_type', 'expense')
            ->whereBetween('transaction_date', [$start, $end])
            ->groupBy('category_id')
            ->with('category:id,name,icon')
            ->get();

        $labels = $data->pluck('category.name');
        $values = $data->pluck('total');

        $colors = [];
        foreach ($data as $item) {
            $hash = crc32($item->category->name);
            $r = ($hash & 0xFF0000) >> 16;
            $g = ($hash & 0x00FF00) >> 8;
            $b = $hash & 0x0000FF;
            $colors[] = "rgb($r, $g, $b)";
        }

        return response()->json(compact('labels', 'values', 'colors'));
    }

    public function incomePie(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = $user->id;
        $range = $request->query('range', 'month');
        $customRange = $request->input('dateRange');
        [$start, $end] = $this->getStartEnd($range, $customRange);

        $data = Transaction::selectRaw('category_id, sum(amount) as total')
            ->where('user_id', $userId)
            ->where('transaction_type', 'income')
            ->whereBetween('transaction_date', [$start, $end])
            ->groupBy('category_id')
            ->with('category:id,name,icon')
            ->get();

        $labels = $data->pluck('category.name');
        $values = $data->pluck('total');

        $colors = [];
        foreach ($data as $item) {
            $hash = crc32($item->category->name);
            $r = ($hash & 0x00FF00) >> 8;
            $g = ($hash & 0xFF0000) >> 16;
            $b = $hash & 0x0000FF;
            $colors[] = "rgb($r, $g, $b)";
        }

        return response()->json(compact('labels', 'values', 'colors'));
    }

    public function summary(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = $user->id;
        $range = $request->query('range', 'month');
        $customRange = $request->input('dateRange');
        [$start, $end] = $this->getStartEnd($range, $customRange);

        $income = Transaction::where('user_id', $userId)
            ->where('transaction_type', 'income')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('amount');

        $expense = Transaction::where('user_id', $userId)
            ->where('transaction_type', 'expense')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('amount');

        return response()->json([
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense
        ]);
    }

    protected function getStartEnd($range, $customRange = null)
    {
        $now = Carbon::now();

        if ($customRange && is_array($customRange) && count($customRange) === 2) {
            $start = Carbon::parse($customRange[0])->startOfDay();
            $end = Carbon::parse($customRange[1])->endOfDay();
            $periods = $this->calculatePeriods($range, $start, $end);
            return [$start->format('Y-m-d'), $end->format('Y-m-d'), $periods];
        }

        switch ($range) {
            case 'week':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                return [$start->format('Y-m-d'), $end->format('Y-m-d'), 7];

            case 'month':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $weeks = ceil($end->copy()->diffInDays($start) / 7);
                return [$start->format('Y-m-d'), $end->format('Y-m-d'), $weeks];

            case 'year':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                return [$start->format('Y-m-d'), $end->format('Y-m-d'), 12];

            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                return [$start->format('Y-m-d'), $end->format('Y-m-d'), 4];
        }
    }


    private function calculatePeriods($range, $start, $end)
    {
        switch ($range) {
            case 'week':
                return 7;
            case 'month':
                return ceil($end->diffInDays($start) / 7);
            case 'year':
                return $end->diffInMonths($start) + 1;
            default:
                return 4;
        }
    }
}
