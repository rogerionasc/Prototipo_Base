<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paciente_convenio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('convenio_id')->constrained('convenios')->cascadeOnDelete();
            $table->string('numero_carteira')->nullable();
            $table->string('plano')->nullable();
            $table->date('validade')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['paciente_id', 'convenio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paciente_convenio');
    }
};
