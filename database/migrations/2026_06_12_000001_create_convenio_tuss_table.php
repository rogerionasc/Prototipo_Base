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
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['convenio_id', 'tuss_id']);
            $table->index(['convenio_id', 'tuss_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenio_tuss');
    }
};

