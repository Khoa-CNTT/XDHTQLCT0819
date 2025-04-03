<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theo_dois', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoidung_id')->constrained()->onDelete('cascade');
            $table->enum('loai_dau_tu', ['co_phieu', 'trai_phieu', 'tien_dien_tu', 'bat_dong_san', 'quy_dau_tu']);
            $table->decimal('so_tien', 15, 2);
            $table->decimal('loi_nhuan', 15, 2)->default(0);
            $table->enum('trang_thai', ['dang_dau_tu', 'da_dong'])->default('dang_dau_tu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theo_dois');
    }
};
