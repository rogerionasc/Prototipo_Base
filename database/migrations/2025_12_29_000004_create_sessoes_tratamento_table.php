<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessoes_tratamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedimento_id')->constrained('procedimentos');
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->unsignedInteger('numero_sessao');
            $table->date('data_prevista')->nullable();
            $table->boolean('realizada')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessoes_tratamento');
    }
};

