<?php

namespace App\Http\Controllers;

use App\Models\Recurringtransaction;
use App\Models\Savingoal;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RecurringtransactionController extends Controller
{
    public function index()
    {
        $transactions = Recurringtransaction::where('user_id', Auth::id())->get();
        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|exists:categories,id',
            'savingoal_id' => 'nullable|exists:savingoals,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'period' => 'required|in:daily,weekly,monthly,yearly',
            'date' => 'required|date',
        ], [
            'category_id.exists' => 'Danh mục đã chọn không hợp lệ.',
            'savingoal_id.exists' => 'Mục tiêu tiết kiệm đã chọn không hợp lệ.',
            'description.required' => 'Vui lòng nhập mô tả.',
            'description.string' => 'Mô tả phải là một chuỗi văn bản.',
            'description.max' => 'Mô tả không được vượt quá 255 ký tự.',
            'amount.required' => 'Vui lòng nhập số tiền.',
            'amount.numeric' => 'Số tiền phải là một số.',
            'period.required' => 'Vui lòng chọn kỳ hạn.',
            'period.in' => 'Kỳ hạn phải là một trong các giá trị: daily, weekly, monthly, yearly.',
            'date.required' => 'Vui lòng chọn ngày.',
            'date.date' => 'Ngày phải có định dạng hợp lệ.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = Auth::id();

        $transaction = Recurringtransaction::create($data);

        return response()->json($transaction, 201);
    }

    public function edit($id)
    {
        $transaction = Recurringtransaction::where('user_id', Auth::id())->find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Giao dịch định kì không tồn tại'], 404);
        }

        return response()->json(['transaction' => $transaction]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|exists:categories,id',
            'savingoal_id' => 'nullable|exists:savingoals,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'period' => 'required|in:daily,weekly,monthly,yearly',
            'date' => 'required|date',
        ], [
            'category_id.exists' => 'Danh mục đã chọn không hợp lệ.',
            'savingoal_id.exists' => 'Mục tiêu tiết kiệm đã chọn không hợp lệ.',
            'description.required' => 'Vui lòng nhập mô tả.',
            'description.string' => 'Mô tả phải là một chuỗi văn bản.',
            'description.max' => 'Mô tả không được vượt quá 255 ký tự.',
            'amount.required' => 'Vui lòng nhập số tiền.',
            'amount.numeric' => 'Số tiền phải là một số.',
            'period.required' => 'Vui lòng chọn kỳ hạn.',
            'period.in' => 'Kỳ hạn phải là một trong các giá trị: daily, weekly, monthly, yearly.',
            'date.required' => 'Vui lòng chọn ngày.',
            'date.date' => 'Ngày phải có định dạng hợp lệ.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $transaction = Recurringtransaction::where('user_id', Auth::id())->find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Giao dịch không tồn tại'], 404);
        }

        $transaction->update($validator->validated());

        return response()->json([
            'message' => 'Ngân sách đã được cập nhật thành công',
            'transaction' => $transaction,
        ]);
    }

    public function destroy($id)
    {
        $transaction = Recurringtransaction::where('user_id', Auth::id())->find($id);

        if (!$transaction) {
            return response()->json(['message' => 'Giao dịch không tồn tại'], 404);
        }

        $transaction->delete();

        return response()->json(['message' => 'Giao dịch đã được xoá thành công']);
    }



    // 
    public function processRecurringTransactions()
    {
        $recurringTransactions = Recurringtransaction::all();

        foreach ($recurringTransactions as $recurringTransaction) {
            $nextTransactionDate = Carbon::parse($recurringTransaction->date);

            if ($this->isTransactionDue($nextTransactionDate, $recurringTransaction->period)) {
                $this->createTransaction($recurringTransaction);

                if ($recurringTransaction->savingoal_id) {
                    $this->updateSavingoal($recurringTransaction);
                }

                $this->updateNextTransactionDate($recurringTransaction);
            }
        }
    }

    private function isTransactionDue(Carbon $transactionDate, $period)
    {
        $today = Carbon::today();

        switch ($period) {
            case 'daily':
                return $transactionDate->isToday();
            case 'weekly':
                return $transactionDate->isSameDay($today->subWeeks(1));
            case 'monthly':
                return $transactionDate->isSameDay($today->subMonths(1));
            case 'yearly':
                return $transactionDate->isSameDay($today->subYears(1));
            default:
                return false;
        }
    }

    private function createTransaction($recurringTransaction)
    {
        Transaction::create([
            'user_id' => $recurringTransaction->user_id,
            'category_id' => $recurringTransaction->category_id,
            'amount' => $recurringTransaction->amount,
            'transaction_date' => Carbon::today(),
            'type' => 'cash',
            'transaction_type' => 'expense',
            'description' => $recurringTransaction->description,
        ]);
    }

    private function updateSavingoal($recurringTransaction)
    {
        $savingoal = Savingoal::find($recurringTransaction->savingoal_id);

        if ($savingoal) {
            $savingoal->save_money_today += $recurringTransaction->amount;
            $savingoal->save_money += $recurringTransaction->amount;

            if ($savingoal->save_money >= $savingoal->target) {
            }

            $savingoal->save();
        }
    }

    private function updateNextTransactionDate($recurringTransaction)
    {
        switch ($recurringTransaction->period) {
            case 'daily':
                $recurringTransaction->date = Carbon::parse($recurringTransaction->date)->addDay();
                break;
            case 'weekly':
                $recurringTransaction->date = Carbon::parse($recurringTransaction->date)->addWeek();
                break;
            case 'monthly':
                $recurringTransaction->date = Carbon::parse($recurringTransaction->date)->addMonth();
                break;
            case 'yearly':
                $recurringTransaction->date = Carbon::parse($recurringTransaction->date)->addYear();
                break;
        }

        $recurringTransaction->save();
    }
}
