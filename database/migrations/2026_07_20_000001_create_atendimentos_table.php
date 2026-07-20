<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atendimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agendamento_id')->constrained('agendamentos');
            $table->foreignId('prontuario_id')->constrained('prontuarios');
            $table->foreignId('profissional_saude_id')->constrained('profissionais_saude');
            $table->foreignId('especialidade_id')->nullable()->constrained('especialidades')->nullOnDelete();
            $table->date('data');
            $table->dateTime('inicio_atendimento')->nullable();
            $table->dateTime('fim_atendimento')->nullable();
            $table->text('evolucao')->nullable();
            $table->string('cid', 10)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atendimentos');
    }
};
