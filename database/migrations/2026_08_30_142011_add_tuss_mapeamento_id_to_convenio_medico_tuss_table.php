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
        Schema::table('convenio_medico_tuss', function (Blueprint $table) {
            $table->dropUnique('cv_med_tuss_unique');
            $table->foreignId('tuss_mapeamento_id')->nullable()->after('tuss_id')->constrained('tuss_mapeamentos')->nullOnDelete();
            $table->unique(['convenio_id', 'pessoa_id', 'tuss_id', 'tuss_mapeamento_id', 'account_id'], 'cv_med_tuss_map_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('convenio_medico_tuss', function (Blueprint $table) {
            $table->dropUnique('cv_med_tuss_map_unique');
            $table->dropForeign(['tuss_mapeamento_id']);
            $table->dropColumn('tuss_mapeamento_id');
            $table->unique(['convenio_id', 'pessoa_id', 'tuss_id', 'account_id'], 'cv_med_tuss_unique');
        });
    }
};
