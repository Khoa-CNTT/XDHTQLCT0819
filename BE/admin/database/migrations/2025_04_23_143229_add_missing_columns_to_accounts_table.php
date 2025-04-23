<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounts', 'number_card')) {
                $table->string('number_card')->unique()->after('type');
            }
            if (!Schema::hasColumn('accounts', 'expired')) {
                $table->date('expired')->after('number_card');
            }
            if (!Schema::hasColumn('accounts', 'pin_code')) {
                $table->string('pin_code')->after('expired');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounts', 'number_card')) {
                $table->dropUnique(['number_card']);
                $table->dropColumn('number_card');
            }
            if (Schema::hasColumn('accounts', 'expired')) {
                $table->dropColumn('expired');
            }
            if (Schema::hasColumn('accounts', 'pin_code')) {
                $table->dropColumn('pin_code');
            }
        });
    }
};
