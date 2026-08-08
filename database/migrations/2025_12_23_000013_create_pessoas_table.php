<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pessoas', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 120);
            $table->string('cpf', 14)->nullable()->index();
            $table->string('rg', 11)->nullable();
            $table->string('sexo', 20)->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('naturalidade', 60)->nullable();
            $table->foreignId('estado_civil_id')->nullable()->constrained('estado_civil')->nullOnDelete();
            $table->string('cnes', 7)->nullable();
            $table->string('cargo')->nullable();
            $table->string('crm', 12)->nullable()->index();
            $table->foreignId('endereco_id')->nullable()->constrained('enderecos')->nullOnDelete();
            $table->string('celular', 15)->nullable();
            $table->string('telefone', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('pessoa_id')->references('id')->on('pessoas')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pessoa_id']);
        });
        Schema::dropIfExists('pessoas');
    }
};
