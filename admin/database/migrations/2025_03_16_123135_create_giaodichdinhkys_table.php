<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('giaodichdinhkys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoidung_id')->constrained()->onDelete('cascade');
            $table->string('ten_giaodich');
            $table->decimal('so_tien', 15, 2);
            $table->foreignId('danhmuc_id')->constrained()->onDelete('cascade');
            $table->enum('chu_ky', ['hang_ngay', 'hang_tuan', 'hang_thang', 'hang_nam']);
            $table->date('ngay_tiep_theo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giaodichdinhkys');
    }
};
