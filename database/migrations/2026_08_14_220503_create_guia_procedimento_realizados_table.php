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
        Schema::create('guia_procedimento_realizados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guia_id')->constrained('guias')->cascadeOnDelete();
            $table->date('data_realizacao')->nullable()->comment('Data realização'); // 36
            $table->time('hora_inicial')->nullable()->comment('Hora inicial'); // 37
            $table->time('hora_final')->nullable()->comment('Hora final'); // 38
            $table->string('tabela_procedimento_realizado', 2)->nullable()->comment('Tabela procedimento realizado'); // 39
            $table->string('procedimento_realizado_codigo', 10)->nullable()->comment('Código procedimento realizado'); // 40
            $table->string('procedimento_realizado_descricao', 150)->nullable()->comment('Descrição procedimento realizado'); // 41
            $table->unsignedTinyInteger('quantidade_realizada')->nullable()->comment('Quantidade realizada'); // 42
            $table->string('via_acesso', 1)->nullable()->comment('Via acesso'); // 43
            $table->string('tecnica_utilizada', 1)->nullable()->comment('Técnica utilizada'); // 44
            $table->decimal('fator_reducao_acrescimo', 3, 2)->nullable()->comment('Fator redução/acréscimo'); // 45
            $table->decimal('valor_unitario', 10, 2)->nullable()->comment('Valor unitário'); // 46
            $table->decimal('valor_total', 10, 2)->nullable()->comment('Valor total'); // 47
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guia_procedimento_realizados');
    }
};
