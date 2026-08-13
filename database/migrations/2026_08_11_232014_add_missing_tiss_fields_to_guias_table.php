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
        Schema::table('guias', function (Blueprint $table) {
            $table->string('cnes_solicitante', 7)->nullable();
            $table->string('cid10_principal', 4)->nullable();
            $table->string('tipo_doenca', 1)->nullable();
            $table->integer('tempo_doenca')->nullable();
            $table->string('unidade_tempo_doenca', 1)->nullable();
            $table->decimal('valor_solicitado', 10, 2)->nullable();
            $table->decimal('valor_autorizado', 10, 2)->nullable();
            $table->string('tipo_acomodacao', 2)->nullable();
            $table->integer('quantidade_diarias')->nullable();
            $table->string('tipo_faturamento', 2)->nullable();
            $table->decimal('taxa_comercializacao', 10, 2)->nullable();
            $table->decimal('total_diarias', 10, 2)->nullable();
            $table->decimal('total_honorarios', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guias', function (Blueprint $table) {
            $table->dropColumn([
                'cnes_solicitante',
                'cid10_principal',
                'tipo_doenca',
                'tempo_doenca',
                'unidade_tempo_doenca',
                'valor_solicitado',
                'valor_autorizado',
                'tipo_acomodacao',
                'quantidade_diarias',
                'tipo_faturamento',
                'taxa_comercializacao',
                'total_diarias',
                'total_honorarios'
            ]);
        });
    }
};
