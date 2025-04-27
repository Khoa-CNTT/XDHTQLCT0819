<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // KHÔNG cần unique
            $table->enum('type', [
                'vietinbank',
                'mbank',
                'sacombank',
                'crypto',
                'vietcombank',
                'vpbank',
                'agribank'
            ]);
            $table->string('number_card'); // Bỏ unique ở đây
            $table->date('expired');
            $table->string('pin_code');
            $table->unique(['type', 'number_card']); // Unique đúng yêu cầu
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
