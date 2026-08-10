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
        Schema::table('atendimentos', function (Blueprint $table) {
            $table->unsignedBigInteger('procedimento_id')->nullable()->change();
            
            if (!Schema::hasColumn('atendimentos', 'tuss_id')) {
                $table->unsignedBigInteger('tuss_id')->nullable()->after('procedimento_id');
                $table->foreign('tuss_id')->references('id')->on('tuss')->onDelete('restrict');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            if (Schema::hasColumn('atendimentos', 'tuss_id')) {
                $table->dropForeign(['tuss_id']);
                $table->dropColumn('tuss_id');
            }
            $table->unsignedBigInteger('procedimento_id')->nullable(false)->change();
        });
    }
};
