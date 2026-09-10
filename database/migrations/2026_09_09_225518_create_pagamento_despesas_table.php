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
        Schema::create('pagamento_despesas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_pagar_id')->constrained('conta_pagars')->cascadeOnDelete();
            $table->date('data_pagamento');
            $table->decimal('valor_pago', 15, 2)->default(0);
            $table->decimal('juros', 15, 2)->default(0);
            $table->decimal('multa', 15, 2)->default(0);
            $table->decimal('desconto', 15, 2)->default(0);
            $table->string('conta_bancaria_id')->nullable();
            $table->string('forma_pagamento_id')->nullable();
            $table->json('anexos')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagamento_despesas');
    }
};
