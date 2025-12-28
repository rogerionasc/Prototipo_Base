<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profissionais_saude', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cpf')->nullable()->index();
            $table->string('rg')->nullable();
            $table->string('sexo')->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('naturalidade')->nullable();
            $table->foreignId('estado_civil_id')->nullable()->constrained('estado_civil')->nullOnDelete();
            $table->string('cnes')->nullable();
            $table->foreignId('endereco_id')->nullable()->constrained('enderecos')->nullOnDelete();
            $table->string('celular')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profissionais_saude');
    }
};
