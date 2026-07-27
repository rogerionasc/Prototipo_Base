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
        Schema::table('pessoas', function (Blueprint $table) {
            if (!Schema::hasColumn('pessoas', 'cargo')) {
                $table->string('cargo')->nullable()->after('cnes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pessoas', function (Blueprint $table) {
            if (Schema::hasColumn('pessoas', 'cargo')) {
                $table->dropColumn('cargo');
            }
        });
    }
};
