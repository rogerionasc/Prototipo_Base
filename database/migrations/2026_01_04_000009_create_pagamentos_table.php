<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pagamentos')) {
            Schema::create('pagamentos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('orcamento_id')->constrained('orcamentos')->cascadeOnDelete();
                $table->foreignId('caixa_id')->nullable()->constrained('caixas')->nullOnDelete();
                $table->foreignId('movimentacao_id')->nullable()->constrained('movimentacoes_caixa')->nullOnDelete();
                $table->decimal('valor', 10, 2)->default(0);
                $table->string('forma_pagamento', 50)->nullable();
                $table->date('data_pagamento')->nullable();
                $table->boolean('confirmado')->default(false);
                $table->string('status', 30)->default('pendente');
                $table->text('recusa_justificativa')->nullable();
                $table->foreignId('recusado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
