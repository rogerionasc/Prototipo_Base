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
        Schema::create('conta_pagars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('despesa_id')->nullable()->constrained('despesas')->cascadeOnDelete();
            $table->string('descricao')->nullable();
            $table->decimal('valor', 15, 2)->default(0);
            $table->foreignId('categoria_id')->nullable()->constrained('categoria_despesas')->nullOnDelete();
            $table->date('data_competencia')->nullable();
            $table->date('data_vencimento')->nullable();
            $table->string('anexo')->nullable();
            $table->string('status')->default('Pendente');
            $table->date('data_pagamento')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conta_pagars');
    }
};
