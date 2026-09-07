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
        Schema::create('comorbidades', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->string('nome');
            $table->timestamps();
        });

        Schema::create('comorbidade_paciente', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->foreignId('comorbidade_id')->constrained('comorbidades')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comorbidade_paciente');
        Schema::dropIfExists('comorbidades');
    }
};
