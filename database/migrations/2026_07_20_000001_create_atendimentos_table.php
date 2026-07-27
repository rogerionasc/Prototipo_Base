<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('atendimentos');
        Schema::create('atendimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->foreignId('convenio_id')->nullable()->constrained('convenios');
            $table->foreignId('medico_id')->constrained('pessoas');
            $table->foreignId('agendamento_id')->nullable()->constrained('agendamentos');
            $table->unsignedBigInteger('autorizacao_id')->nullable(); // nullable
            $table->unsignedBigInteger('guia_id')->nullable(); // nullable
            $table->foreignId('caixa_pagamento_id')->nullable()->constrained('pagamentos');
            $table->foreignId('procedimento_id')->constrained('procedimentos');
            $table->foreignId('categoria_procedimento_id')->constrained('categorias_procedimento');
            
            $table->string('tipo_atendimento', 50)->nullable(); // Particular ou Convenio
            $table->date('data_atendimento')->nullable();
            $table->dateTime('hora_prevista')->nullable();
            $table->dateTime('hora_inicio')->nullable();
            $table->dateTime('hora_fim')->nullable();
            $table->string('prioridade', 50)->nullable();
            $table->string('status', 50)->default('AGENDADO');
            $table->text('observacao')->nullable();
            $table->text('motivo_cancelamento')->nullable();
            
            $table->foreignId('criado_por')->nullable()->constrained('users');
            $table->foreignId('atualizado_por')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('atendimentos');
    }
};
