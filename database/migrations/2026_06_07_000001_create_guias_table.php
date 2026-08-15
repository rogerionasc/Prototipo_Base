<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faturamento_id')->nullable()->constrained('faturamentos')->nullOnDelete()->nullable()->comment('Lote (Faturamento)');
            $table->string('status', 30)->default('CRIADA')->nullable()->comment('CRIADA, EM_ANALISE, AUTORIZADA, GLOSADA, PAGA, CANCELADA');
            $table->string('tipo')->nullable()->default('Guia de Consulta');

            // DADOS DA GUIA
            $table->string('ans_registro', 6)->nullable()->comment('Registro ANS'); // 1
            $table->string('numero_guia_prestador', 20)->nullable()->comment('Número da guia no prestador'); // 2
            $table->string('numero_guia_principal', 20)->nullable()->comment('Número da guia principal'); // 3
            $table->date('data_autorizacao')->nullable()->comment('Data da autorização'); // 4
            $table->string('senha', 20)->nullable()->comment('Senha'); // 5
            $table->date('data_validade_senha')->nullable()->comment('Data validade senha'); // 6
            $table->string('numero_guia_operadora', 20)->nullable()->comment('Número guia operadora'); // 7

            // DADOS DO BENEFICIÁRIO
            $table->string('numero_carteira', 20)->nullable()->comment('Número carteira'); // 8
            $table->date('validade_carteira')->nullable()->comment('Validade carteira'); // 9
            $table->string('beneficiario_nome', 70)->nullable()->comment('Nome beneficiário'); // 10
            $table->string('cns', 15)->nullable()->comment('CNS'); // 11
            $table->boolean('atendimento_rn')->default(false)->nullable()->comment('Atendimento RN (S/N)'); // 12

            // DADOS DO SOLICITANTE
            $table->string('contratado_solicitante_codigo', 14)->nullable()->comment('Código contratado solicitante'); // 13
            $table->string('contratado_solicitante_nome', 70)->nullable()->comment('Nome contratado solicitante'); // 14
            $table->string('profissional_solicitante_nome', 70)->nullable()->comment('Nome profissional solicitante'); // 15
            $table->string('conselho_solicitante', 2)->nullable()->comment('Conselho solicitante'); // 16
            $table->string('numero_conselho_solicitante', 15)->nullable()->comment('Número conselho solicitante'); // 17
            $table->string('uf_conselho_solicitante', 2)->nullable()->comment('UF conselho solicitante'); // 18
            $table->string('cbo_solicitante', 6)->nullable()->comment('CBO solicitante'); // 19
            $table->text('assinatura_profissional_solicitante')->nullable()->comment('Assinatura profissional solicitante'); // 20

            // DADOS DA SOLICITAÇÃO/PROCEDIMENTO
            $table->string('carater_atendimento', 1)->nullable()->comment('Caráter atendimento'); // 21
            $table->date('data_solicitacao')->nullable()->comment('Data solicitação'); // 22
            $table->text('indicacao_clinica')->nullable()->comment('Indicação clínica'); // 23

            // DADOS TISS ADICIONADOS POSTERIORMENTE
            $table->string('cnes_solicitante', 7)->nullable();
            $table->string('cid10_principal', 4)->nullable();
            $table->string('tipo_doenca', 1)->nullable();
            $table->integer('tempo_doenca')->nullable();
            $table->string('unidade_tempo_doenca', 1)->nullable();
            $table->decimal('valor_solicitado', 10, 2)->nullable();
            $table->decimal('valor_autorizado', 10, 2)->nullable();
            $table->string('tipo_acomodacao', 2)->nullable();
            $table->integer('quantidade_diarias')->nullable();
            $table->string('tipo_faturamento', 2)->nullable();

            // DADOS DO CONTRATADO EXECUTANTE
            $table->string('contratado_executante_codigo', 14)->nullable()->comment('Código contratado executante'); // 29
            $table->string('contratado_executante_nome', 70)->nullable()->comment('Nome contratado executante'); // 30
            $table->string('cnes_executante', 7)->nullable()->comment('CNES executante'); // 31
            $table->string('tipo_atendimento', 2)->nullable()->comment('Tipo atendimento'); // 32
            $table->string('indicacao_acidente', 1)->nullable()->comment('Indicação acidente'); // 33
            $table->string('tipo_consulta', 1)->nullable()->comment('Tipo consulta'); // 34
            $table->string('motivo_encerramento', 2)->nullable()->comment('Motivo encerramento'); // 35
            $table->text('assinatura_beneficiario_serie')->nullable()->comment('Assinatura beneficiário série'); // 57
            $table->text('observacao_justificativa')->nullable()->comment('Observação/Justificativa'); // 58

            // TOTAIS
            $table->decimal('total_procedimentos', 10, 2)->nullable()->comment('Total procedimentos'); // 59
            $table->decimal('total_taxas_alugueis', 10, 2)->nullable()->comment('Total taxas/aluguéis'); // 60
            $table->decimal('total_materiais', 10, 2)->nullable()->comment('Total materiais'); // 61
            $table->decimal('total_opme', 10, 2)->nullable()->comment('Total OPME'); // 62
            $table->decimal('total_medicamentos', 10, 2)->nullable()->comment('Total medicamentos'); // 63
            $table->decimal('total_gases_medicinais', 10, 2)->nullable()->comment('Total gases medicinais'); // 64
            $table->decimal('taxa_comercializacao', 10, 2)->nullable();
            $table->decimal('total_diarias', 10, 2)->nullable();
            $table->decimal('total_honorarios', 10, 2)->nullable();
            $table->decimal('valor_total_geral', 10, 2)->nullable()->comment('Valor total geral'); // 65
            $table->decimal('valor_glosado', 10, 2)->nullable()->comment('Valor Glosado');

            // ASSINATURAS
            $table->text('assinatura_responsavel_autorizacao')->nullable()->comment('Assinatura responsável autorização'); // 66
            $table->text('assinatura_beneficiario')->nullable()->comment('Assinatura beneficiário'); // 67
            $table->text('assinatura_contratado')->nullable()->comment('Assinatura contratado'); // 68

            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('guias');
    }
};
