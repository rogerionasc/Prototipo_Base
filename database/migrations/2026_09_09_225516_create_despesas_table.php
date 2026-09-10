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
        Schema::create('despesas', function (Blueprint $table) {
            $table->id();
            $table->string('fornecedor_id')->nullable(); // String for prototype, standard is foreignId
            $table->string('categoria_id')->nullable();
            $table->string('centro_custo_id')->nullable();
            $table->string('descricao')->nullable();
            $table->string('documento_nf')->nullable();
            $table->date('data_competencia')->nullable();
            $table->date('data_emissao')->nullable();
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->json('anexos')->nullable();
            $table->string('recorrencia_tipo')->nullable(); // mensal, semanal
            $table->date('recorrencia_fim')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despesas');
    }
};
