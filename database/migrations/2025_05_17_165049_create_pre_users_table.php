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
        Schema::create('pre_cadastro', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 120);
            $table->string('sobrenome', 120);
            $table->string('cpf', 14)->unique();
            $table->string('telefone', 15);
            $table->date('data_nascimento');
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_cadastro');
    }
};
