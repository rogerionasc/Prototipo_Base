<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('profissional_especialidade')) {
            Schema::create('profissional_especialidade', function (Blueprint $table) {
                $table->id();
                $table->foreignId('profissional_saude_id')->constrained('profissionais_saude');
                $table->foreignId('especialidade_id')->constrained('especialidades');
                $table->string('qre', 6)->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['profissional_saude_id', 'especialidade_id'], 'ps_esp_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('profissional_especialidade');
    }
};
