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
        Schema::create('pep_tratamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pep_id')->constrained('peps')->onDelete('cascade');
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('profissional_id')->nullable()->constrained('pessoas')->onDelete('set null');
            $table->string('nome_tratamento', 255);
            $table->integer('quantidade_sessoes_previstas')->default(1);
            $table->integer('quantidade_sessoes_realizadas')->default(0);
            $table->string('status', 50)->default('Em andamento');
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pep_tratamentos');
    }
};
