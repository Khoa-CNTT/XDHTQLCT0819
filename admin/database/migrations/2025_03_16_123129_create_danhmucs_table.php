<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danhmucs', function (Blueprint $table) {
            $table->id();
            $table->string('ten'); // Tên danh mục
            $table->enum('loai', ['thu_nhap', 'chi_tieu']); // Loại danh mục
            $table->timestamps(); // Ngày tạo và cập nhật
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danhmucs');
    }
};
