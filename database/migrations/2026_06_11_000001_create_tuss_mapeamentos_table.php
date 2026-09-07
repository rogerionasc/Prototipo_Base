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
        Schema::create('tuss_mapeamentos', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->unsignedBigInteger('account_id')->index();
            $table->unsignedBigInteger('origem_procedimento_id')->comment('O procedimento que será mapeado,ex: TUSS 10101012');
            $table->unsignedBigInteger('referencia_procedimento_id')->comment('O procedimento de referência, ex: AMB92 00010014');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('origem_procedimento_id')->references('id')->on('tuss')->onDelete('cascade');
            $table->foreign('referencia_procedimento_id')->references('id')->on('tuss')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tuss_mapeamentos');
    }
};
