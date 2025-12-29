<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_medica_id')->constrained('agenda_medica');
            $table->date('data');
            $table->time('hora');
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->foreignId('procedimento_id')->nullable()->constrained('procedimentos')->nullOnDelete();
            $table->foreignId('status_id')->nullable()->constrained('status_agendamento')->nullOnDelete();
            $table->unsignedBigInteger('agendamento_origem_id')->nullable();
            $table->decimal('valor_cobrado', 10, 2)->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('agendamento_origem_id')->references('id')->on('agendamentos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendamentos');
    }
};
