<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convenio_tuss', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->constrained('convenios');
            $table->foreignId('tuss_id')->constrained('tuss');
            $table->boolean('requer_autorizacao')->default(false);
            $table->decimal('valor_ch', 10, 2)->default(0); //Valor CH
            $table->decimal('valor_co', 10, 2)->default(0); //Valor do CO
            $table->decimal('valor_procedimento', 10, 2)->default(0); //valor_rocedimento= (quantidade_ch * valor_ch) + (quantidade_co * valor_co)

            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->unique(['convenio_id', 'tuss_id', 'account_id']);
            $table->index(['convenio_id', 'tuss_id', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenio_tuss');
    }
};
