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
            $table->foreignId('faturamento_id')->nullable()->constrained('faturamentos')->nullOnDelete()->comment('Lote (Faturamento)');

            // DADOS DA GUIA
            $table->string('ans_registro', 6)->comment('Registro ANS'); // 1
            $table->string('numero_guia_prestador', 20)->comment('Número da guia no prestador'); // 2
            $table->string('numero_guia_principal', 20)->nullable()->comment('Número da guia principal'); // 3
            $table->date('data_autorizacao')->nullable()->comment('Data da autorização'); // 4
            $table->string('senha', 20)->nullable()->comment('Senha'); // 5
            $table->date('data_validade_senha')->nullable()->comment('Data validade senha'); // 6
            $table->string('numero_guia_operadora', 20)->nullable()->comment('Número guia operadora'); // 7

            // DADOS DO BENEFICIÁRIO
            $table->string('numero_carteira', 20)->comment('Número carteira'); // 8
            $table->date('validade_carteira')->nullable()->comment('Validade carteira'); // 9
            $table->string('beneficiario_nome', 70)->comment('Nome beneficiário'); // 10
            $table->string('cns', 15)->nullable()->comment('CNS'); // 11
            $table->boolean('atendimento_rn')->default(false)->comment('Atendimento RN (S/N)'); // 12

            // DADOS DO SOLICITANTE
            $table->string('contratado_solicitante_codigo', 14)->comment('Código contratado solicitante'); // 13
            $table->string('contratado_solicitante_nome', 70)->comment('Nome contratado solicitante'); // 14
            $table->string('profissional_solicitante_nome', 70)->nullable()->comment('Nome profissional solicitante'); // 15
            $table->string('conselho_solicitante', 2)->comment('Conselho solicitante'); // 16
            $table->string('numero_conselho_solicitante', 15)->comment('Número conselho solicitante'); // 17
            $table->string('uf_conselho_solicitante', 2)->comment('UF conselho solicitante'); // 18
            $table->string('cbo_solicitante', 6)->comment('CBO solicitante'); // 19
            $table->text('assinatura_profissional_solicitante')->comment('Assinatura profissional solicitante'); // 20

            // DADOS DA SOLICITAÇÃO/PROCEDIMENTO
            $table->string('carater_atendimento', 1)->comment('Caráter atendimento'); // 21
            $table->date('data_solicitacao')->nullable()->comment('Data solicitação'); // 22
            $table->text('indicacao_clinica')->nullable()->comment('Indicação clínica'); // 23
            $table->string('tabela_procedimento_solicitado', 2)->comment('Tabela procedimento solicitado'); // 24
            $table->string('procedimento_solicitado_codigo', 10)->comment('Código procedimento solicitado'); // 25
            $table->string('procedimento_solicitado_descricao', 150)->comment('Descrição procedimento solicitado'); // 26
            $table->unsignedTinyInteger('quantidade_solicitada')->comment('Quantidade solicitada'); // 27
            $table->unsignedTinyInteger('quantidade_autorizada')->comment('Quantidade autorizada'); // 28

            // DADOS DO CONTRATADO EXECUTANTE
            $table->string('contratado_executante_codigo', 14)->comment('Código contratado executante'); // 29
            $table->string('contratado_executante_nome', 70)->comment('Nome contratado executante'); // 30
            $table->string('cnes_executante', 7)->comment('CNES executante'); // 31
            $table->string('tipo_atendimento', 2)->comment('Tipo atendimento'); // 32
            $table->string('indicacao_acidente', 1)->comment('Indicação acidente'); // 33
            $table->string('tipo_consulta', 1)->nullable()->comment('Tipo consulta'); // 34
            $table->string('motivo_encerramento', 2)->nullable()->comment('Motivo encerramento'); // 35
            $table->date('data_realizacao')->nullable()->comment('Data realização'); // 36
            $table->time('hora_inicial')->nullable()->comment('Hora inicial'); // 37
            $table->time('hora_final')->nullable()->comment('Hora final'); // 38

            // PROCEDIMENTO REALIZADO
            $table->string('tabela_procedimento_realizado', 2)->nullable()->comment('Tabela procedimento realizado'); // 39
            $table->string('procedimento_realizado_codigo', 10)->nullable()->comment('Código procedimento realizado'); // 40
            $table->string('procedimento_realizado_descricao', 150)->nullable()->comment('Descrição procedimento realizado'); // 41
            $table->unsignedTinyInteger('quantidade_realizada')->nullable()->comment('Quantidade realizada'); // 42
            $table->string('via_acesso', 1)->nullable()->comment('Via acesso'); // 43
            $table->string('tecnica_utilizada', 1)->nullable()->comment('Técnica utilizada'); // 44
            $table->decimal('fator_reducao_acrescimo', 3, 2)->nullable()->comment('Fator redução/acréscimo'); // 45
            $table->decimal('valor_unitario', 10, 2)->nullable()->comment('Valor unitário'); // 46
            $table->decimal('valor_total', 10, 2)->nullable()->comment('Valor total'); // 47

            // IDENTIFICAÇÃO DOS PROFISSIONAIS EXECUTANTES
            $table->string('sequencial_referencia', 2)->nullable()->comment('Sequencial referência'); // 48
            $table->string('grau_participacao', 2)->nullable()->comment('Grau participação'); // 49
            $table->string('profissional_executante_codigo', 14)->nullable()->comment('Código/CPF executante'); // 50
            $table->string('profissional_executante_nome', 70)->nullable()->comment('Nome profissional executante'); // 51
            $table->string('conselho_executante', 2)->nullable()->comment('Conselho executante'); // 52
            $table->string('numero_conselho_executante', 15)->nullable()->comment('Número conselho executante'); // 53
            $table->string('uf_conselho_executante', 2)->nullable()->comment('UF conselho executante'); // 54
            $table->string('cbo_executante', 6)->nullable()->comment('CBO executante'); // 55
            $table->date('data_realizacao_serie')->nullable()->comment('Data realização série'); // 56
            $table->text('assinatura_beneficiario_serie')->comment('Assinatura beneficiário série'); // 57
            $table->text('observacao_justificativa')->nullable()->comment('Observação/Justificativa'); // 58

            // TOTAIS
            $table->decimal('total_procedimentos', 10, 2)->nullable()->comment('Total procedimentos'); // 59
            $table->decimal('total_taxas_alugueis', 10, 2)->nullable()->comment('Total taxas/aluguéis'); // 60
            $table->decimal('total_materiais', 10, 2)->nullable()->comment('Total materiais'); // 61
            $table->decimal('total_opme', 10, 2)->nullable()->comment('Total OPME'); // 62
            $table->decimal('total_medicamentos', 10, 2)->nullable()->comment('Total medicamentos'); // 63
            $table->decimal('total_gases_medicinais', 10, 2)->nullable()->comment('Total gases medicinais'); // 64
            $table->decimal('valor_total_geral', 10, 2)->comment('Valor total geral'); // 65

            // ASSINATURAS
            $table->text('assinatura_responsavel_autorizacao')->comment('Assinatura responsável autorização'); // 66
            $table->text('assinatura_beneficiario')->comment('Assinatura beneficiário'); // 67
            $table->text('assinatura_contratado')->comment('Assinatura contratado'); // 68

            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('guias');
    }
};
