<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faturamentos', function (Blueprint $table) {
            $table->dropColumn('agendamento_id');
        });

        Schema::table('guias', function (Blueprint $table) {
            $table->string('status', 30)->default('CRIADA')->after('faturamento_id')->comment('CRIADA, EM_ANALISE, AUTORIZADA, GLOSADA, PAGA, CANCELADA');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guias', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('faturamentos', function (Blueprint $table) {
            $table->unsignedBigInteger('agendamento_id')->nullable()->after('paciente_id');
            $table->foreign('agendamento_id')->references('id')->on('agendamentos')->cascadeOnDelete();
        });
    }
};
