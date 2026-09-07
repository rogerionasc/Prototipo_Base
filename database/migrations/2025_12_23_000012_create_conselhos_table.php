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
        Schema::create('conselhos', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->string('sigla', 10)->unique()->comment('Ex: CRM, CRO, COREN');
            $table->string('codigo', 2)->nullable()->comment('Código TISS');
            $table->string('descricao', 100)->nullable()->comment('Descrição opcional');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conselhos');
    }
};
