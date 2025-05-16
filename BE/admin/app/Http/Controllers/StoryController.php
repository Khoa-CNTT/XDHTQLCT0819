<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoryController extends Controller
{

    public function getstories()
    {
        $userId = Auth::id();

        $stories = Story::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($stories);
    }

    public function destroy(Request $request)
    {
        $userId = Auth::id();
        $ids = $request->input('ids');

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['message' => 'Danh sách ID không hợp lệ.'], 400);
        }

        $deleted = Story::where('user_id', $userId)
            ->whereIn('id', $ids)
            ->delete();

        return response()->json(['message' => "Đã xoá $deleted hoạt động."]);
    }
}
