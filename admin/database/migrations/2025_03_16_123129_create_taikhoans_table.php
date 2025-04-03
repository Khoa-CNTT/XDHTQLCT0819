<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taikhoans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoidung_id')->constrained()->onDelete('cascade'); // Quan hệ với bảng nguoidungs
            $table->string('ten_taikhoan'); // Tên tài khoản
            $table->enum('loai_taikhoan', ['ngan_hang', 'vi_dien_tu', 'tien_mat', 'tien_dien_tu']); // Loại tài khoản
            $table->decimal('so_du', 15, 2)->default(0); // Số dư
            $table->timestamps(); // Ngày tạo và cập nhật
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taikhoans');
    }
};
