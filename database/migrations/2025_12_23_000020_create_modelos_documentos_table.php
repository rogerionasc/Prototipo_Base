<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modelos_documentos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 50);
            $table->string('nome', 120);
            $table->text('conteudo_template')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modelos_documentos');
    }
};
