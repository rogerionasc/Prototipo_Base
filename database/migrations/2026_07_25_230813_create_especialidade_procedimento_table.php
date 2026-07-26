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
        Schema::create('especialidade_procedimento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('especialidade_id')->constrained('especialidades')->onDelete('cascade');
            $table->foreignId('procedimento_id')->constrained('procedimentos')->onDelete('cascade');
            $table->timestamps();
        });

        // Migrate existing data
        DB::statement('
            INSERT INTO especialidade_procedimento (especialidade_id, procedimento_id, created_at, updated_at)
            SELECT especialidade_id, id, NOW(), NOW()
            FROM procedimentos
            WHERE especialidade_id IS NOT NULL
        ');

        Schema::table('procedimentos', function (Blueprint $table) {
            $table->dropForeign(['especialidade_id']);
            $table->dropColumn('especialidade_id');
        });
    }

    public function down(): void
    {
        Schema::table('procedimentos', function (Blueprint $table) {
            $table->foreignId('especialidade_id')->nullable()->constrained('especialidades')->onDelete('set null');
        });

        // Try to reverse migrate data if possible
        DB::statement('
            UPDATE procedimentos p
            JOIN especialidade_procedimento ep ON ep.procedimento_id = p.id
            SET p.especialidade_id = ep.especialidade_id
        ');

        Schema::dropIfExists('especialidade_procedimento');
    }
};
