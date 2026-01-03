<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orcamentos', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique();
            $table->date('data_emissao')->nullable();
            $table->date('validade')->nullable();
            $table->foreignId('profissional_saude_id')->constrained('profissionais_saude');
            $table->foreignId('convenio_id')->nullable()->constrained('convenios')->nullOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->decimal('valor_bruto', 10, 2)->default(0);
            $table->decimal('desconto', 10, 2)->default(0);
            $table->decimal('valor_total', 10, 2)->default(0);
            $table->decimal('valor_avista', 10, 2)->nullable();
            $table->boolean('faturamento_previsto')->default(false);
            $table->boolean('aprovado')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orcamentos');
    }
};

