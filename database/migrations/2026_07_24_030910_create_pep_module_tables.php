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
        // 1. peps
        Schema::create('peps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atendimento_id')->constrained('atendimentos');
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->foreignId('profissional_id')->constrained('pessoas');
            $table->dateTime('aberto_em')->nullable();
            $table->dateTime('encerrado_em')->nullable();
            $table->string('status', 50)->default('Aberto');
            $table->text('observacao')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // 2. pep_anamneses
        Schema::create('pep_anamneses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pep_id')->constrained('peps')->onDelete('cascade');
            $table->text('queixa_principal')->nullable();
            $table->text('historia_doenca_atual')->nullable();
            $table->text('antecedentes_pessoais')->nullable();
            $table->text('antecedentes_familiares')->nullable();
            $table->text('historico_social')->nullable();
            $table->text('alergias')->nullable();
            $table->text('medicamentos_uso')->nullable();
            $table->text('habitos_vida')->nullable();
            $table->text('observacao')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // 3. pep_sinais_vitais
        Schema::create('pep_sinais_vitais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pep_id')->constrained('peps')->onDelete('cascade');
            $table->string('pressao_sistolica', 20)->nullable();
            $table->string('pressao_diastolica', 20)->nullable();
            $table->string('frequencia_cardiaca', 20)->nullable();
            $table->string('frequencia_respiratoria', 20)->nullable();
            $table->string('temperatura', 20)->nullable();
            $table->string('saturacao', 20)->nullable();
            $table->decimal('peso', 5, 2)->nullable();
            $table->decimal('altura', 3, 2)->nullable();
            $table->decimal('imc', 5, 2)->nullable();
            $table->string('glicemia', 20)->nullable();
            $table->string('circunferencia_abdominal', 20)->nullable();
            $table->text('observacao')->nullable();
            $table->foreignId('profissional_id')->nullable()->constrained('pessoas');
            $table->timestamps();
        });

        // 4. pep_exames_fisicos
        Schema::create('pep_exames_fisicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pep_id')->constrained('peps')->onDelete('cascade');
            $table->text('descricao')->nullable();
            $table->text('observacao')->nullable();
            $table->foreignId('profissional_id')->nullable()->constrained('pessoas');
            $table->timestamps();
        });

        // 5. pep_diagnosticos
        Schema::create('pep_diagnosticos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pep_id')->constrained('peps')->onDelete('cascade');
            $table->unsignedBigInteger('cid_id')->nullable();
            $table->boolean('principal')->default(false);
            $table->text('descricao')->nullable();
            $table->boolean('confirmado')->default(false);
            $table->foreignId('profissional_id')->nullable()->constrained('pessoas');
            $table->timestamps();
        });

        // 6. pep_evolucoes
        Schema::create('pep_evolucoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pep_id')->constrained('peps')->onDelete('cascade');
            $table->foreignId('profissional_id')->nullable()->constrained('pessoas');
            $table->string('tipo', 100)->nullable();
            $table->text('descricao')->nullable();
            $table->timestamps();
        });

        // 7. pep_procedimentos
        Schema::create('pep_procedimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pep_id')->constrained('peps')->onDelete('cascade');
            $table->foreignId('procedimento_id')->nullable()->constrained('procedimentos');
            $table->integer('quantidade')->default(1);
            $table->text('observacao')->nullable();
            $table->foreignId('profissional_id')->nullable()->constrained('pessoas');
            $table->dateTime('realizado_em')->nullable();
            $table->timestamps();
        });

        // 8. pep_prescricoes
        Schema::create('pep_prescricoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pep_id')->constrained('peps')->onDelete('cascade');
            $table->foreignId('profissional_id')->nullable()->constrained('pessoas');
            $table->text('observacao')->nullable();
            $table->date('validade')->nullable();
            $table->timestamps();
        });

        // 9. pep_prescricao_itens
        Schema::create('pep_prescricao_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescricao_id')->constrained('pep_prescricoes')->onDelete('cascade');
            $table->unsignedBigInteger('medicamento_id')->nullable();
            $table->string('dosagem', 100)->nullable();
            $table->string('frequencia', 100)->nullable();
            $table->string('via', 100)->nullable();
            $table->string('duracao', 100)->nullable();
            $table->integer('quantidade')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        // 10. pep_solicitacoes_exames
        Schema::create('pep_solicitacoes_exames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pep_id')->constrained('peps')->onDelete('cascade');
            $table->foreignId('procedimento_id')->nullable()->constrained('procedimentos');
            $table->text('justificativa')->nullable();
            $table->boolean('urgente')->default(false);
            $table->string('status', 50)->default('Solicitado');
            $table->foreignId('profissional_id')->nullable()->constrained('pessoas');
            $table->timestamps();
        });

        // 11. pep_receitas
        Schema::create('pep_receitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pep_id')->constrained('peps')->onDelete('cascade');
            $table->foreignId('prescricao_id')->nullable()->constrained('pep_prescricoes')->onDelete('set null');
            $table->text('texto')->nullable();
            $table->dateTime('emitido_em')->nullable();
            $table->foreignId('profissional_id')->nullable()->constrained('pessoas');
            $table->timestamps();
        });

        // 12. pep_atestados
        Schema::create('pep_atestados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pep_id')->constrained('peps')->onDelete('cascade');
            $table->integer('dias')->nullable();
            $table->unsignedBigInteger('cid_id')->nullable();
            $table->text('texto')->nullable();
            $table->dateTime('emitido_em')->nullable();
            $table->foreignId('profissional_id')->nullable()->constrained('pessoas');
            $table->timestamps();
        });

        // 13. pep_encaminhamentos
        Schema::create('pep_encaminhamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pep_id')->constrained('peps')->onDelete('cascade');
            $table->string('especialidade_destino', 255)->nullable();
            $table->string('profissional_destino', 255)->nullable();
            $table->text('motivo')->nullable();
            $table->text('observacao')->nullable();
            $table->foreignId('profissional_id')->nullable()->constrained('pessoas');
            $table->timestamps();
        });

        // 14. pep_documentos
        Schema::create('pep_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pep_id')->constrained('peps')->onDelete('cascade');
            $table->string('tipo', 100)->nullable();
            $table->string('titulo', 255)->nullable();
            $table->longText('conteudo')->nullable();
            $table->dateTime('emitido_em')->nullable();
            $table->foreignId('profissional_id')->nullable()->constrained('pessoas');
            $table->timestamps();
        });

        // 15. pep_arquivos
        Schema::create('pep_arquivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pep_id')->constrained('peps')->onDelete('cascade');
            $table->string('nome', 255)->nullable();
            $table->string('arquivo', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->integer('tamanho')->nullable();
            $table->text('observacao')->nullable();
            $table->string('enviado_por', 255)->nullable();
            $table->timestamps();
        });

        // 16. pep_assinaturas
        Schema::create('pep_assinaturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pep_id')->constrained('peps')->onDelete('cascade');
            $table->unsignedBigInteger('documento_id')->nullable();
            $table->foreignId('profissional_id')->nullable()->constrained('pessoas');
            $table->string('tipo_documento', 100)->nullable();
            $table->text('hash_documento')->nullable();
            $table->text('certificado')->nullable();
            $table->dateTime('assinado_em')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pep_assinaturas');
        Schema::dropIfExists('pep_arquivos');
        Schema::dropIfExists('pep_documentos');
        Schema::dropIfExists('pep_encaminhamentos');
        Schema::dropIfExists('pep_atestados');
        Schema::dropIfExists('pep_receitas');
        Schema::dropIfExists('pep_solicitacoes_exames');
        Schema::dropIfExists('pep_prescricao_itens');
        Schema::dropIfExists('pep_prescricoes');
        Schema::dropIfExists('pep_procedimentos');
        Schema::dropIfExists('pep_evolucoes');
        Schema::dropIfExists('pep_diagnosticos');
        Schema::dropIfExists('pep_exames_fisicos');
        Schema::dropIfExists('pep_sinais_vitais');
        Schema::dropIfExists('pep_anamneses');
        Schema::dropIfExists('peps');
    }
};
