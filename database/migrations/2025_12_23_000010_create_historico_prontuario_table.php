<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('historico_prontuario')) {
            Schema::create('historico_prontuario', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('atendimento_id');
                $table->dateTime('data_registro')->nullable();
                $table->text('descricao')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_prontuario');
    }
};
