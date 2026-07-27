<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convenio_medico_tuss', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->constrained('convenios');
            $table->foreignId('pessoa_id')->constrained('pessoas');
            $table->foreignId('tuss_id')->nullable()->constrained('tuss');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['convenio_id', 'pessoa_id', 'tuss_id'], 'cv_med_tuss_unique');
            $table->index(['convenio_id', 'pessoa_id']);
            $table->index(['convenio_id', 'tuss_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenio_medico_tuss');
    }
};
