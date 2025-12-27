<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historico_prontuario', function (Blueprint $table) {
            $table->foreign('atendimento_id')->references('id')->on('atendimentos')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('historico_prontuario', function (Blueprint $table) {
            $table->dropForeign(['atendimento_id']);
        });
    }
};
