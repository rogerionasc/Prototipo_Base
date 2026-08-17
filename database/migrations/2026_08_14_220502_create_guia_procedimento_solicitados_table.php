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
        Schema::create('guia_procedimento_solicitados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guia_id')->constrained('guias')->cascadeOnDelete();
            $table->string('tabela_procedimento_solicitado', 2)->nullable()->comment('Tabela procedimento solicitado'); // 24
            $table->string('procedimento_solicitado_codigo', 10)->nullable()->comment('Código procedimento solicitado'); // 25
            $table->string('procedimento_solicitado_descricao', 150)->nullable()->comment('Descrição procedimento solicitado'); // 26
            $table->unsignedTinyInteger('quantidade_solicitada')->nullable()->comment('Quantidade solicitada'); // 27
            $table->unsignedTinyInteger('quantidade_autorizada')->nullable()->comment('Quantidade autorizada'); // 28
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guia_procedimento_solicitados');
    }
};
