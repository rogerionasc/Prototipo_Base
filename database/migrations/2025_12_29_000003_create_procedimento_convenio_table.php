<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedimento_convenio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedimento_id')->constrained('procedimentos')->cascadeOnDelete();
            $table->foreignId('convenio_id')->constrained('convenios')->cascadeOnDelete();
            $table->decimal('valor_convenio', 10, 2)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['procedimento_id', 'convenio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedimento_convenio');
    }
};

