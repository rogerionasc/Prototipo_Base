<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('movimentacoes_caixa')) {
            Schema::create('movimentacoes_caixa', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();

                $table->id();
                $table->foreignId('caixa_id')->constrained('caixas');
                $table->foreignId('aberto_por_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('numero', 50)->unique();
                $table->datetime('data_movimento');
                $table->decimal('total_entradas', 12, 2)->default(0);
                $table->decimal('total_saidas', 12, 2)->default(0);
                $table->decimal('saldo_caixa', 12, 2)->default(0);
                $table->decimal('total_entrada_prazo', 12, 2)->default(0);
                $table->decimal('total_saida_prazo', 12, 2)->default(0);
                $table->decimal('total_transferencia', 12, 2)->default(0);
                $table->decimal('total_conferencia', 12, 2)->default(0);
                $table->decimal('saldo_movimento', 12, 2)->default(0);
                $table->decimal('valor_diferenca', 12, 2)->default(0);
                $table->datetime('fechado_em')->nullable();
                $table->foreignId('fechado_por_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('reaberto_por_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('observacoes_fechamento')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentacoes_caixa');
    }
};
