<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('muctieus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoidung_id')->constrained()->onDelete('cascade');
            $table->string('ten');
            $table->decimal('muc_tieu_tien', 15, 2);
            $table->decimal('tien_hien_tai', 15, 2)->default(0);
            $table->date('han_chot');
            $table->enum('trang_thai', ['dang_thuc_hien', 'hoan_thanh', 'that_bai'])->default('dang_thuc_hien');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('muctieus');
    }
};
