<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convenios', function (Blueprint $table) {
            $table->id();
            $table->string('descricao', 120);
            $table->string('tipo', 20)->nullable();
            $table->foreignId('empresa_id')->nullable()->constrained('contas')->nullOnDelete();
            $table->unsignedInteger('ans')->nullable();
            $table->unsignedInteger('dias_recebimento')->nullable();
            $table->unsignedInteger('dias_retorno')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenios');
    }
};
