<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            if (!Schema::hasColumn('agendamentos', 'sessao_tratamento_id')) {
                $table->foreignId('sessao_tratamento_id')->nullable()->constrained('sessoes_tratamento')->nullOnDelete()->after('procedimento_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            if (Schema::hasColumn('agendamentos', 'sessao_tratamento_id')) {
                $table->dropConstrainedForeignId('sessao_tratamento_id');
            }
        });
    }
};

