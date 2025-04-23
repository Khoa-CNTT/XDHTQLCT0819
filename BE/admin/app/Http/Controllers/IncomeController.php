<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Income;

class IncomeController extends Controller
{
    public function index()
    {
        return Income::where('user_id', Auth::id())->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string|max:255',
        ]);

        $validated['user_id'] = Auth::id();
        return Income::create($validated);
    }
}

