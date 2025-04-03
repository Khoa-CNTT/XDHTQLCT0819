<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phan_tichs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoidung_id')->constrained()->onDelete('cascade');
            $table->enum('loai_phan_tich', ['xu_huong_chi_tieu', 'phat_hien_bat_thuong', 'toi_uu_ngan_sach']);
            $table->json('ket_qua');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phan_tichs');
    }
};
