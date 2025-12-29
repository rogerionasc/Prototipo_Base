<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 120);
            $table->string('cpf', 14)->nullable()->index();
            $table->string('rg', 11)->nullable();
            $table->string('sexo', 20)->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('naturalidade', 60)->nullable();
            $table->foreignId('estado_civil_id')->nullable()->constrained('estado_civil')->nullOnDelete();
            $table->decimal('altura', 5, 2)->nullable();
            $table->decimal('peso', 5, 2)->nullable();
            $table->string('cor_pele', 20)->nullable();
            $table->foreignId('endereco_id')->nullable()->constrained('enderecos')->nullOnDelete();
            $table->boolean('receber_avisos')->default(false);
            $table->string('celular', 15)->nullable();
            $table->string('telefone', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->foreignId('canal_aviso_id')->nullable()->constrained('canais_aviso')->nullOnDelete();
            $table->string('profissao', 60)->nullable();
            $table->string('escolaridade', 60)->nullable();
            $table->string('nome_mae', 120)->nullable();
            $table->string('nome_pai', 120)->nullable();
            $table->foreignId('tipo_sanguineo_id')->nullable()->constrained('tipo_sanguineo')->nullOnDelete();
            $table->text('observacoes')->nullable();
            $table->boolean('tem_responsavel')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
