<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('caixas')) {
            Schema::create('caixas', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();

                $table->id();
                $table->string('descricao', 120);
                $table->string('tipo', 50);
                $table->boolean('bloquear_receber')->default(false);
                $table->boolean('bloquear_pagar')->default(false);
                $table->string('link_display')->nullable();
                $table->boolean('ativo')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('caixas');
    }
};
