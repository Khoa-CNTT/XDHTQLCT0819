<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Lấy danh sách danh mục",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách danh mục",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="icon", type="string"),
     *                 @OA\Property(property="user_id", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $categories = Category::where('user_id', Auth::id())->get();
        return response()->json($categories);
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Tạo danh mục mới",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "type"},
     *             @OA\Property(property="name", type="string", example="Chi tiêu"),
     *             @OA\Property(property="type", type="string", example="expense"),
     *             @OA\Property(property="icon", type="string", example="icon-wallet")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tạo thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tạo danh mục thành công"),
     *             @OA\Property(property="category", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|unique:categories,slug',
            'type' => 'required|in:income,expense',
            'icon' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        $data = $request->only(['name','slug', 'type', 'icon']);
        $data['user_id'] = Auth::id();

        $category = Category::create($data);

        return response()->json([
            'message' => 'Tạo danh mục thành công',
            'category' => $category
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     summary="Xem chi tiết danh mục",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true, @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin danh mục",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="icon", type="string"),
     *             @OA\Property(property="user_id", type="integer")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $category = Category::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Không tìm thấy danh mục'], 404);
        }

        return response()->json($category);
    }

    /**
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     summary="Cập nhật danh mục",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true, @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Chi tiêu sửa"),
     *             @OA\Property(property="type", type="string", example="expense"),
     *             @OA\Property(property="icon", type="string", example="icon-new")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cập nhật thành công"),
     *             @OA\Property(property="category", type="object")
     *         )
     *     )
     * )
     */
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
            'slug' => 'required|unique:categories,slug',
            'type' => 'required|in:income,expense',
            'icon' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category->update($request->only(['name', 'type', 'icon']));

        return response()->json([
            'message' => 'Cập nhật danh mục thành công',
            'category' => $category
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     summary="Xóa danh mục",
     *     tags={"Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true, @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Xóa thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Xóa danh mục thành công")
     *         )
     *     )
     * )
     */
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
