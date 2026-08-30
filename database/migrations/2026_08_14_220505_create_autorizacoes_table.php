<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autorizacoes', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();

            $table->id()->startingValue(1000);
            $table->foreignId('convenio_id')->constrained()->onDelete('cascade');
            $table->string('protocolo', 100)->nullable();
            $table->foreignId('guia_id')->nullable()->constrained('guias')->cascadeOnDelete();
            $table->foreignId('procedimento_solicitado_id')->nullable()->constrained('guia_procedimento_solicitados')->cascadeOnDelete();
            $table->foreignId('tuss_id')->nullable()->constrained('tuss')->nullOnDelete();
            $table->decimal('valor', 10, 2)->nullable();
            $table->string('numero_autorizacao', 100)->nullable();
            $table->enum('status', ['SOLICITADA', 'AUTORIZADA', 'Pendente', 'Aprovada', 'Negada', 'Expirada', 'Cancelada'])->default('Pendente');
            $table->date('validade')->nullable();
            $table->timestamp('data_solicitacao')->nullable();
            $table->timestamp('data_resposta')->nullable();
            $table->text('observacao')->nullable();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('usuario_id_validou')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autorizacoes');
    }
};
