<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescricoes', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();

            $table->id();
            $table->foreignId('prontuario_id')->constrained('prontuarios');
            $table->foreignId('pessoa_id')->constrained('pessoas');
            $table->dateTime('data_prescricao')->nullable();
            $table->text('prescricao')->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('ativa')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescricoes');
    }
};
