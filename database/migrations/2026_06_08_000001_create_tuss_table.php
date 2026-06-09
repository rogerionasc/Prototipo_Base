<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tuss', function (Blueprint $table) {
            $table->id();

            $table->string('tabela', 20)->comment('AMB1990|AMB1992|AMB1993|AMB1999|CBHPM3|CBHPM4|CBHPM5|TUSS');
            $table->string('codigo', 30)->comment('Código na tabela informada');
            $table->string('descricao', 255)->nullable();

            $table->decimal('m2_filme', 10, 2)->nullable()->comment('m² Filme');
            $table->decimal('auxiliares', 10, 2)->nullable();
            $table->decimal('incidencia', 10, 2)->nullable();
            $table->string('porte', 20)->nullable();
            $table->decimal('ch', 10, 2)->nullable();
            $table->decimal('co', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable()->comment('CH + CO');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tabela', 'codigo']);
            $table->index(['tabela', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tuss');
    }
};
