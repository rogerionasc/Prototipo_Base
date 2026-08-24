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
        $tables = [
            'atendimentos', 'prontuarios', 'agenda_medica', 'orcamentos', 
            'caixas', 'movimentacoes_caixa', 'pagamentos', 'sessoes_tratamento', 
            'autorizacoes', 'responsaveis', 'modelos_documentos', 'documentos_prontuario', 
            'prescricoes', 'solicitacao_exames', 'salas', 'guiches', 'paineis', 
            'totens', 'especialidades'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'account_id')) {
                        $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remaining_tenant_tables', function (Blueprint $table) {
            //
        });
    }
};
