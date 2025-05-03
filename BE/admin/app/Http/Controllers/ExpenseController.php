<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index()
    {
        return Expense::with('category')
            ->where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'category_id' => 'required|exists:categories,id',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'note' => 'nullable|string|max:255',
        ]);

        $expense = Expense::create([
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'time' => $validated['time'],
            'note' => $validated['note'] ?? null,
        ]);

        return response()->json($expense->load('category'), 201);
    }
    public function getExpensesByCategory($categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return response()->json(['error' => 'Danh mục không tồn tại'], 404);
        }

        $expenses = Expense::where('category_id', $categoryId)
            ->where('user_id', Auth::id()) 
            ->get();

        return response()->json($expenses);
    }
}
