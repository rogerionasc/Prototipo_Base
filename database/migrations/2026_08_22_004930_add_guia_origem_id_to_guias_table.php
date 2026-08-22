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
        Schema::table('guias', function (Blueprint $table) {
            $table->unsignedBigInteger('guia_origem_id')->nullable()->after('id');
            $table->foreign('guia_origem_id')->references('id')->on('guias')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guias', function (Blueprint $table) {
            $table->dropForeign(['guia_origem_id']);
            $table->dropColumn('guia_origem_id');
        });
    }
};
