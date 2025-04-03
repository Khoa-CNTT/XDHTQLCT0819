<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ngansachs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoidung_id')->constrained()->onDelete('cascade');
            $table->foreignId('danhmuc_id')->constrained()->onDelete('cascade');
            $table->decimal('gioi_han_ngansach', 15, 2);
            $table->decimal('nguong_canh_bao', 15, 2)->nullable();
            $table->decimal('goi_y_ai', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ngansachs');
    }
};
