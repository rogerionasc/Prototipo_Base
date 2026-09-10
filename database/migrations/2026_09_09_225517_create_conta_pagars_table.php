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
            $table->foreignId('despesa_id')->nullable()->constrained('despesas')->nullOnDelete();
            $table->string('fornecedor_id')->nullable();
            $table->string('categoria_id')->nullable();
            $table->string('centro_custo_id')->nullable();
            $table->string('descricao')->nullable();
            $table->integer('parcela_num')->default(1);
            $table->date('data_vencimento')->nullable();
            $table->decimal('valor_base', 15, 2)->default(0);
            $table->decimal('desconto', 15, 2)->default(0);
            $table->decimal('juros', 15, 2)->default(0);
            $table->decimal('multa', 15, 2)->default(0);
            $table->decimal('valor_pago', 15, 2)->default(0);
            $table->string('status')->default('Pendente'); // Pendente, Vencida, Paga, Cancelada, Agendada
            $table->date('data_pagamento')->nullable();
            $table->string('forma_pagamento_id')->nullable();
            $table->string('conta_bancaria_id')->nullable();
            $table->json('anexos')->nullable();
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
