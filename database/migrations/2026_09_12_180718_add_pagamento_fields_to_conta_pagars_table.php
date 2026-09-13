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
        Schema::table('conta_pagars', function (Blueprint $table) {
            $table->decimal('valor_pago', 15, 2)->nullable();
            $table->foreignId('configuracao_bancaria_id')->nullable()->constrained('configuracoes_bancarias')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conta_pagars', function (Blueprint $table) {
            $table->dropForeign(['configuracao_bancaria_id']);
            $table->dropColumn(['valor_pago', 'configuracao_bancaria_id']);
        });
    }
};
