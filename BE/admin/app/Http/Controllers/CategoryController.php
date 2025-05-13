<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\UserActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use UserActivityLogger;
    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = Category::where('user_id', $userId);

        if ($request->has('type') && in_array($request->type, ['income', 'expense'])) {
            $query->where('type', $request->type);
        }

        $categories = $query->get();

        foreach ($categories as $category) {
            $total = DB::table('transactions')
                ->where('category_id', $category->id)
                ->where('user_id', $userId)
                ->sum('amount');

            $category->total_amount = $total ?: 0;
        }

        $otherIncomeTotal = DB::table('transactions')
            ->whereNull('category_id')
            ->where('user_id', $userId)
            ->where('transaction_type', 'income')
            ->sum('amount');

        if ($otherIncomeTotal > 0 && (!$request->has('type') || $request->type == 'income')) {
            $otherIncome = new \stdClass();
            $otherIncome->id = -1;
            $otherIncome->name = 'Khác ( Thu )';
            $otherIncome->icon = 'fas fa-question';
            $otherIncome->slug = 'khac-thu';
            $otherIncome->type = 'income';
            $otherIncome->user_id = $userId;
            $otherIncome->total_amount = $otherIncomeTotal;

            $otherIncome->transactions = DB::table('transactions')
                ->whereNull('category_id')
                ->where('user_id', $userId)
                ->where('transaction_type', 'income')
                ->orderBy('created_at', 'desc')
                ->get();

            $categories->push($otherIncome);
        }

        $otherExpenseTotal = DB::table('transactions')
            ->whereNull('category_id')
            ->where('user_id', $userId)
            ->where('transaction_type', 'expense')
            ->sum('amount');

        if ($otherExpenseTotal > 0 && (!$request->has('type') || $request->type == 'expense')) {
            $otherExpense = new \stdClass();
            $otherExpense->id = -2;
            $otherExpense->name = 'Khác ( Chi )';
            $otherExpense->icon = 'fas fa-question';
            $otherExpense->slug = 'khac-chi';
            $otherExpense->type = 'expense';
            $otherExpense->user_id = $userId;
            $otherExpense->total_amount = $otherExpenseTotal;

            $otherExpense->transactions = DB::table('transactions')
                ->whereNull('category_id')
                ->where('user_id', $userId)
                ->where('transaction_type', 'expense')
                ->orderBy('created_at', 'desc')
                ->get();

            $categories->push($otherExpense);
        }

        if ($request->has('type')) {
            $categories = $categories->filter(function ($category) use ($request) {
                return $category->type == $request->type;
            });
        }

        return response()->json($categories);
    }

    public function showHome(Request $request)
    {
        $userId = Auth::id();
        $type = $request->type;
        $name = $request->name;

        $query = DB::table('categories')
            ->join('transactions', 'categories.id', '=', 'transactions.category_id')
            ->where('categories.user_id', $userId)
            ->where('transactions.user_id', $userId)
            ->select(
                'categories.id',
                'categories.name',
                'categories.icon',
                'categories.slug',
                'categories.type',
                'categories.user_id',
                'categories.created_at',
                'categories.updated_at',
                DB::raw('SUM(transactions.amount) as total_amount')
            )
            ->groupBy(
                'categories.id',
                'categories.name',
                'categories.icon',
                'categories.slug',
                'categories.type',
                'categories.user_id',
                'categories.created_at',
                'categories.updated_at'
            );

        if ($type && in_array($type, ['income', 'expense'])) {
            $query->where('categories.type', $type);
        }

        if ($name) {
            $query->where('categories.name', 'like', '%' . $name . '%');
        }

        $query->having('total_amount', '>', 0);

        $categories = $query->get();

        //  "Khác thu"
        $otherIncomeTotal = DB::table('transactions')
            ->whereNull('category_id')
            ->where('user_id', $userId)
            ->where('transaction_type', 'income')
            ->sum('amount');

        if ($otherIncomeTotal > 0 && (!$type || $type == 'income')) {
            $otherIncome = new \stdClass();
            $otherIncome->id = -1;
            $otherIncome->name = 'Khác ( Thu )';
            $otherIncome->icon = 'fas fa-question';
            $otherIncome->slug = 'khac-thu';
            $otherIncome->type = 'income';
            $otherIncome->user_id = $userId;
            $otherIncome->total_amount = $otherIncomeTotal;
            $categories->push($otherIncome);
        }

        //  "Khác chi"
        $otherExpenseTotal = DB::table('transactions')
            ->whereNull('category_id')
            ->where('user_id', $userId)
            ->where('transaction_type', 'expense')
            ->sum('amount');

        if ($otherExpenseTotal > 0 && (!$type || $type == 'expense')) {
            $otherExpense = new \stdClass();
            $otherExpense->id = -2;
            $otherExpense->name = 'Khác ( Chi )';
            $otherExpense->icon = 'fas fa-question';
            $otherExpense->slug = 'khac-chi';
            $otherExpense->type = 'expense';
            $otherExpense->user_id = $userId;
            $otherExpense->total_amount = $otherExpenseTotal;

            $otherExpense->transactions = DB::table('transactions')
                ->whereNull('category_id')
                ->where('user_id', $userId)
                ->where('transaction_type', 'expense')
                ->orderBy('created_at', 'desc')
                ->get();

            $categories->push($otherExpense);
        }

        return response()->json($categories);
    }




    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'icon' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $slug = Str::slug($request->name);
        $slugExists = Category::where('slug', $slug)->exists();
        if ($slugExists) {
            return response()->json(['message' => 'Danh Mục đã tồn tại. Vui lòng chọn Danh Mục khác.'], 422);
        }

        $category = Category::create([
            'name' => $request->name,
            'slug' => $slug,
            'type' => $request->type,
            'icon' => $request->icon,
            'user_id' => Auth::id(),
        ]);
        $this->logAction('Đã thêm mới danh mục: ' . $category->name . 'thành công');
        return response()->json([
            'message' => 'Tạo danh mục thành công',
            'category' => $category
        ]);
    }

    public function show($id)
    {
        $userId = Auth::id();

        if ((int)$id === -1) {
            // Khác thu
            $transactions = DB::table('transactions')
                ->whereNull('category_id')
                ->where('user_id', $userId)
                ->where('transaction_type', 'income')
                ->get();

            $totalAmount = $transactions->sum('amount');

            $otherCategory = (object)[
                'id' => -1,
                'name' => 'Khác ( Thu )',
                'icon' => 'fas fa-question',
                'slug' => 'khac-thu',
                'type' => 'income',
                'user_id' => $userId,
                'created_at' => null,
                'updated_at' => null,
            ];

            return response()->json([
                'category' => $otherCategory,
                'transactions' => $transactions,
                'total_amount' => $totalAmount,
            ]);
        }

        if ((int)$id === -2) {
            // Khác chi
            $transactions = DB::table('transactions')
                ->whereNull('category_id')
                ->where('user_id', $userId)
                ->where('transaction_type', 'expense')
                ->get();

            $totalAmount = $transactions->sum('amount');

            $otherCategory = (object)[
                'id' => -2,
                'name' => 'Khác ( Chi )',
                'icon' => 'fas fa-question',
                'slug' => 'khac-chi',
                'type' => 'expense',
                'user_id' => $userId,
                'created_at' => null,
                'updated_at' => null,
            ];

            return response()->json([
                'category' => $otherCategory,
                'transactions' => $transactions,
                'total_amount' => $totalAmount,
            ]);
        }

        $category = Category::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Không tìm thấy danh mục'], 404);
        }

        $transactions = DB::table('transactions')
            ->where('category_id', $category->id)
            ->where('user_id', $userId)
            ->get();

        $totalAmount = $transactions->sum('amount');

        return response()->json([
            'category' => $category,
            'transactions' => $transactions,
            'total_amount' => $totalAmount,
        ]);
    }





    public function update(Request $request, $id)
    {
        $category = Category::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Không tìm thấy danh mục'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'icon' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $slug = Str::slug($request->name);
        $slugExists = Category::where('slug', $slug)->where('id', '!=', $category->id)->exists();
        if ($slugExists) {
            return response()->json(['message' => 'Danh Mục đã tồn tại. Vui lòng chọn Danh Mục khác.'], 422);
        }

        $category->update([
            'name' => $request->name,
            'slug' => $slug,
            'type' => $request->type,
            'icon' => $request->icon,
        ]);
        $this->logAction('Đã cập nhật danh mục: ' . $category->name . ' thành công');

        return response()->json([
            'message' => 'Cập nhật danh mục thành công',
            'category' => $category
        ]);
    }

    public function destroy($id)
    {
        $userId = Auth::id();

        $category = Category::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Không tìm thấy danh mục'], 404);
        }

        $transactions = Transaction::where('category_id', $category->id)->get();

        $user = User::find($userId);

        foreach ($transactions as $transaction) {
            if ($transaction->transaction_type === 'income') {
                $user->monthly_income -= $transaction->amount;
                $user->monthly_customer_spending -= $transaction->amount;
            } elseif ($transaction->transaction_type === 'expense') {
                $user->monthly_customer_spending += $transaction->amount;
            }
        }

        $user->save();
        Transaction::where('category_id', $category->id)->delete();
        $category->delete();
        $this->logAction('Đã xoá danh mục: ' . $category->name . ' thành công');
        return response()->json([
            'message' => 'Xóa danh mục và các giao dịch liên quan thành công, đã cập nhật số liệu',
            'monthly_income' => $user->monthly_income,
            'monthly_customer_spending' => $user->monthly_customer_spending,
        ]);
    }
}
