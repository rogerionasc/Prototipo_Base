<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enderecos', function (Blueprint $table) {
            $table->id();
            $table->string('cep', 9)->nullable();
            $table->string('endereco', 120);
            $table->string('numero', 10)->nullable();
            $table->string('bairro', 60)->nullable();
            $table->string('cidade', 60)->nullable();
            $table->string('complemento', 60)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enderecos');
    }
};
