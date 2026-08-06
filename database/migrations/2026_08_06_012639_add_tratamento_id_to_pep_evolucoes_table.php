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
        Schema::table('pep_evolucoes', function (Blueprint $table) {
            $table->foreignId('tratamento_id')->nullable()->after('pep_id')->constrained('pep_tratamentos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pep_evolucoes', function (Blueprint $table) {
            $table->dropForeign(['tratamento_id']);
            $table->dropColumn('tratamento_id');
        });
    }
};
