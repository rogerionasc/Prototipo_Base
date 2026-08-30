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
            $table->integer('sessao')->nullable()->after('status')->comment('Identificador da sessão do tratamento se aplicável');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atendimentos', function (Blueprint $table) {
            $table->dropColumn('sessao');
        });
    }
};
