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
        Schema::create('guia_profissional_executantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guia_id')->constrained('guias')->cascadeOnDelete();
            $table->string('sequencial_referencia', 2)->nullable()->comment('Sequencial referência'); // 48
            $table->string('grau_participacao', 2)->nullable()->comment('Grau participação'); // 49
            $table->string('profissional_executante_codigo', 14)->nullable()->comment('Código/CPF executante'); // 50
            $table->string('profissional_executante_nome', 70)->nullable()->comment('Nome profissional executante'); // 51
            $table->string('conselho_executante', 2)->nullable()->comment('Conselho executante'); // 52
            $table->string('numero_conselho_executante', 15)->nullable()->comment('Número conselho executante'); // 53
            $table->string('uf_conselho_executante', 2)->nullable()->comment('UF conselho executante'); // 54
            $table->string('cbo_executante', 6)->nullable()->comment('CBO executante'); // 55
            $table->date('data_realizacao_serie')->nullable()->comment('Data realização série'); // 56
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guia_profissional_executantes');
    }
};
