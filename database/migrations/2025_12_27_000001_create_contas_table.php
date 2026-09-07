<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contas', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->string('nome', 120);
            $table->string('cnpj', 18)->nullable();
            $table->string('pix_chave', 140)->nullable();
            $table->string('pix_nome', 120)->nullable();
            $table->string('pix_cidade', 120)->nullable();
            $table->string('pix_descricao', 140)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contas');
    }
};
