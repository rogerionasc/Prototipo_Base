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
        Schema::table('tuss', function (Blueprint $table) {
            $table->dropColumn(['eh_tratamento', 'quantidade_sessoes']);
        });

        Schema::table('convenio_tuss', function (Blueprint $table) {
            $table->boolean('eh_tratamento')->default(false)->after('requer_autorizacao');
            $table->integer('quantidade_sessoes')->nullable()->after('eh_tratamento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('convenio_tuss', function (Blueprint $table) {
            $table->dropColumn(['eh_tratamento', 'quantidade_sessoes']);
        });

        Schema::table('tuss', function (Blueprint $table) {
            $table->boolean('eh_tratamento')->default(false);
            $table->integer('quantidade_sessoes')->nullable();
        });
    }
};
