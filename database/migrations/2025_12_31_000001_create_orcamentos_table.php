<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orcamentos', function (Blueprint $table) {

            $table->id();
            $table->string('numero', 30)->unique();
            $table->datetime('data_emissao')->nullable();
            $table->datetime('validade')->nullable();
            $table->foreignId('convenio_id')->nullable()->constrained('convenios')->nullOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->decimal('valor_bruto', 10, 2)->default(0);
            $table->decimal('desconto', 10, 2)->default(0);
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->decimal('valor_avista', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        if (!Schema::hasTable('faturamentos')) {
            Schema::create('faturamentos', function (Blueprint $table) {
            $table->string('numero_lote', 20)->nullable();

            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();

                $table->id();
                $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
                $table->foreignId('convenio_id')->nullable()->constrained('convenios')->nullOnDelete();
                $table->decimal('valor_total', 10, 2)->default(0);
                $table->decimal('valor_final', 10, 2)->default(0);
                $table->decimal('valor_cobrado', 10, 2)->default(0);
                $table->decimal('valor_aprovado', 10, 2)->default(0);
                $table->decimal('valor_glosado', 10, 2)->default(0);
                $table->string('status', 30)->default('AGUARDANDO_PAGAMENTO');
                $table->datetime('data_faturamento')->nullable();
                $table->date('vencimento')->nullable();
                $table->timestamps();

                $table->index(['paciente_id', 'status']);
                $table->index(['convenio_id', 'status']);
            });
        }

        if (!Schema::hasTable('contas_receber')) {
            Schema::create('contas_receber', function (Blueprint $table) {
                $table->id();
                $table->foreignId('faturamento_id')->constrained('faturamentos')->cascadeOnDelete();
                $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
                $table->foreignId('convenio_id')->nullable()->constrained('convenios')->nullOnDelete();
                $table->decimal('valor', 10, 2)->default(0);
                $table->date('vencimento')->nullable();
                $table->string('status', 20)->default('ABERTO');
                $table->timestamps();

                $table->index(['paciente_id', 'status']);
                $table->index(['faturamento_id', 'status']);
                $table->index(['convenio_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contas_receber');
        Schema::dropIfExists('faturamentos');
        Schema::dropIfExists('orcamentos');
    }
};
