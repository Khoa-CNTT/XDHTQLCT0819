<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
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

        $otherTotal = DB::table('transactions')
            ->whereNull('category_id')
            ->where('user_id', $userId)
            ->when($request->has('type') && in_array($request->type, ['income', 'expense']), function ($q) use ($request) {
                $q->where('transaction_type', $request->type);
            })
            ->sum('amount');

        if ($otherTotal > 0) {
            $otherCategory = new \stdClass();
            $otherCategory->id = -1;
            $otherCategory->name = 'Khác';
            $otherCategory->icon = 'fas fa-question';
            $otherCategory->slug = 'khac';
            $otherCategory->type = $request->type ?? null;
            $otherCategory->user_id = $userId;
            $otherCategory->total_amount = $otherTotal;

            $categories->push($otherCategory);
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

        $otherTotal = DB::table('transactions')
            ->whereNull('category_id')
            ->where('user_id', $userId)
            ->when($type && in_array($type, ['income', 'expense']), function ($q) use ($type) {
                $q->where('transaction_type', $type);
            })
            ->sum('amount');

        if ($otherTotal > 0) {
            $otherCategory = new \stdClass();
            $otherCategory->id = -1;
            $otherCategory->name = 'Khác';
            $otherCategory->icon = 'fas fa-question';
            $otherCategory->slug = 'khac';
            $otherCategory->type = $type ?? null;
            $otherCategory->user_id = $userId;
            $otherCategory->total_amount = $otherTotal;

            $categories->push($otherCategory);
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

        return response()->json([
            'message' => 'Tạo danh mục thành công',
            'category' => $category
        ]);
    }

    public function show($id)
    {
        $userId = Auth::id();

        if ((int)$id === -1) {
            $transactions = DB::table('transactions')
                ->whereNull('category_id')
                ->where('user_id', $userId)
                ->get();

            $totalAmount = $transactions->sum('amount');

            $otherCategory = (object)[
                'id' => -1,
                'name' => 'Khác',
                'icon' => 'fas fa-question',
                'slug' => 'khac',
                'type' => null,
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

        // Xử lý danh mục bình thường
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

        return response()->json([
            'message' => 'Xóa danh mục và các giao dịch liên quan thành công, đã cập nhật số liệu',
            'monthly_income' => $user->monthly_income,
            'monthly_customer_spending' => $user->monthly_customer_spending,
        ]);
    }
}
