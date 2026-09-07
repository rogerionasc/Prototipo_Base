<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        if (!Schema::hasTable('pagamentos')) {
            Schema::create('pagamentos', function (Blueprint $table) {

                $table->id()->startingValue(1000);
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
                $table->string('nu_pagamento', 25)->nullable();
                $table->foreignId('faturamento_id')->constrained('faturamentos')->cascadeOnDelete();
                $table->foreignId('agendamento_id')->nullable()->constrained('agendamentos')->nullOnDelete();
                $table->foreignId('caixa_id')->nullable()->constrained('caixas')->nullOnDelete();
                $table->foreignId('movimentacao_id')->nullable()->constrained('movimentacoes_caixa')->nullOnDelete();
                $table->decimal('valor', 10, 2)->default(0);
                $table->string('forma_pagamento', 50)->nullable();
                $table->datetime('data_pagamento')->nullable();
                $table->string('status', 30)->default('PENDENTE');
                $table->text('recusa_justificativa')->nullable();
                $table->foreignId('recusado_por')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
