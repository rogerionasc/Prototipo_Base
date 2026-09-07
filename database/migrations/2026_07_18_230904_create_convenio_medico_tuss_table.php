<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convenio_medico_tuss', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('convenio_id')->constrained('convenios');
            $table->foreignId('pessoa_id')->constrained('pessoas');
            $table->foreignId('tuss_id')->nullable()->constrained('tuss');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['convenio_id', 'pessoa_id', 'tuss_id', 'account_id'], 'cv_med_tuss_unique');
            $table->index(['convenio_id', 'pessoa_id', 'account_id'], 'cv_med_acc_idx');
            $table->index(['convenio_id', 'tuss_id', 'account_id'], 'cv_tuss_acc_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenio_medico_tuss');
    }
};
