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
        Schema::table('configuracoes_bancarias', function (Blueprint $table) {
            $table->dropColumn('carteira');
            $table->string('numero_convenio', 30)->nullable()->after('ambiente');
            $table->string('numero_carteira', 20)->nullable()->after('numero_convenio');
            $table->string('numero_variacao_carteira', 20)->nullable()->after('numero_carteira');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracoes_bancarias', function (Blueprint $table) {
            $table->dropColumn(['numero_convenio', 'numero_carteira', 'numero_variacao_carteira']);
            $table->string('carteira', 20)->nullable()->after('ambiente');
        });
    }
};
