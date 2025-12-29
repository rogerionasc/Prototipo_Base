<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_prontuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prontuario_id')->constrained('prontuarios');
            $table->foreignId('modelo_documento_id')->constrained('modelos_documentos');
            $table->foreignId('profissional_saude_id')->constrained('profissionais_saude');
            $table->dateTime('data_emissao')->nullable();
            $table->text('conteudo_final')->nullable();
            $table->boolean('assinado')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_prontuario');
    }
};
