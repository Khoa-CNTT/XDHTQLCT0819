<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DanhMucController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Danh mục đã được lấy thành công',
            'data' => [
                'danh_muc' => [
                    'id' => 1,
                    'ten_danh_muc' => 'Tiền mặt',
                    'mo_ta' => 'Tiền mặt',
                    'created_at' => '2021-01-01 00:00:00',
                    'updated_at' => '2021-01-01 00:00:00',
                ],
            ],
        ]);
    }
}
