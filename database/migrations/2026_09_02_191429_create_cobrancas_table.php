<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cobrancas', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('conta_receber_id')->constrained('contas_receber')->cascadeOnDelete();
            $table->foreignId('configuracao_bancaria_id')->constrained('configuracoes_bancarias')->restrictOnDelete();
            $table->string('gateway', 50);
            $table->string('gateway_id', 150)->nullable();
            $table->string('tipo', 30)->default('boleto');
            $table->string('nosso_numero', 100)->nullable();
            $table->string('linha_digitavel', 255)->nullable();
            $table->string('codigo_barras', 255)->nullable();
            $table->string('pix_txid', 150)->nullable();
            $table->string('url', 255)->nullable();
            $table->decimal('valor', 10, 2);
            $table->date('vencimento');
            $table->string('status', 30)->default('REGISTRADA');
            $table->datetime('data_pagamento')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cobrancas');
    }
};
