<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dulieudauvaos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoidung_id')->constrained()->onDelete('cascade');
            $table->string('ten_nguon');
            $table->decimal('so_tien', 15, 2);
            $table->date('ngay_nhan');
            $table->enum('loai_dulieudauvao', ['luong', 'dau_tu', 'kinh_doanh', 'khac']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dulieudauvaos');
    }
};
