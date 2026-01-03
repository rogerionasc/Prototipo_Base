<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedimentos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 120);
            $table->string('descricao', 120)->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_procedimento')->nullOnDelete();
            $table->boolean('eh_tratamento')->default(false);
            $table->unsignedInteger('quantidade_sessoes')->nullable();
            $table->decimal('valor', 10, 2)->nullable();
            $table->decimal('comissao_percentual', 5, 2)->default(0)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedimentos');
    }
};
