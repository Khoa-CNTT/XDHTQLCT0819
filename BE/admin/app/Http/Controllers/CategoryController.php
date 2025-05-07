<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::where('user_id', Auth::id());

        if ($request->has('type') && in_array($request->type, ['income', 'expense'])) {
            $query->where('type', $request->type);
        }

        $categories = $query->get();

        foreach ($categories as $category) {
            $total = DB::table('transactions')
                ->where('category_id', $category->id)
                ->where('user_id', Auth::id())
                ->sum('amount');

            $category->total_amount = $total ?: 0;
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
        $category = Category::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Không tìm thấy danh mục'], 404);
        }

        $transactions = DB::table('transactions')
            ->where('category_id', $category->id)
            ->where('user_id', Auth::id())
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
        $category = Category::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Không tìm thấy danh mục'], 404);
        }

        $category->delete();
        return response()->json(['message' => 'Xóa danh mục thành công']);
    }
}
