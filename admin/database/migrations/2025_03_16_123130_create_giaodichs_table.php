<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('giaodichs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoidung_id')->constrained()->onDelete('cascade');
            $table->foreignId('taikhoan_id')->constrained()->onDelete('cascade');
            $table->foreignId('danhmuc_id')->constrained()->onDelete('cascade');
            $table->decimal('so_tien', 15, 2);
            $table->date('ngay_giao_dich');
            $table->enum('phuong_thuc', ['tien_mat', 'the_tin_dung', 'the_ghi_no', 'vi_dien_tu', 'tien_dien_tu']);
            $table->string('dia_diem')->nullable();
            $table->boolean('la_dinh_ky')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giaodichs');
    }
};
