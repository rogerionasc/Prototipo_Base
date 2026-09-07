<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes_bancarias', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->string('provedor', 50)->comment('mock, asaas, bb, etc.');
            $table->string('tipo', 30)->comment('boleto, pix, etc.');
            $table->string('ambiente', 20)->default('sandbox');
            $table->string('carteira', 20)->nullable();
            $table->text('client_id')->nullable();
            $table->text('client_secret')->nullable();
            $table->text('certificado')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes_bancarias');
    }
};
