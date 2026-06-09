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

            $table->string('ans_reg', 20)->nullable()->comment('Registro ANS');
            $table->string('guia_prest', 30)->nullable()->comment('Nº Guia no Prestador');
            $table->string('guia_princ', 30)->nullable()->comment('Nº Guia Principal');
            $table->date('dt_aut')->nullable()->comment('Data da Autorização');
            $table->string('senha', 30)->nullable()->comment('Senha');
            $table->date('dt_val_senha')->nullable()->comment('Validade da Senha');
            $table->string('guia_oper', 30)->nullable()->comment('Nº Guia (Operadora)');
            $table->string('benef_nome', 120)->nullable()->comment('Nome do Beneficiário');
            $table->date('cart_val')->nullable()->comment('Validade da Carteira');
            $table->string('nome_titular', 120)->nullable()->comment('Nome');
            $table->string('cns', 15)->nullable()->comment('Cartão Nacional de Saúde');
            $table->boolean('rn')->default(false)->comment('Atendimento a RN');
            $table->string('contr_nome', 120)->nullable()->comment('Nome do Contratado');
            $table->string('contr_cod_op', 30)->nullable()->comment('Código do Contratado (Operadora)');
            $table->string('sol_nome', 120)->nullable()->comment('Profissional Solicitante');
            $table->string('sol_cons', 30)->nullable()->comment('Conselho (Solicitante)');
            $table->string('sol_cons_num', 30)->nullable()->comment('Nº Conselho (Solicitante)');
            $table->char('sol_uf', 2)->nullable()->comment('UF (Solicitante)');
            $table->string('sol_cbo', 10)->nullable()->comment('CBO (Solicitante)');
            $table->text('sol_ass')->nullable()->comment('Assinatura (Solicitante)');
            $table->string('carater', 30)->nullable()->comment('Caráter do Atendimento');
            $table->string('solicit_num', 30)->nullable()->comment('Nº da Solicitação');
            $table->text('ind_clin')->nullable()->comment('Indicação Clínica');
            $table->text('atend_dados')->nullable()->comment('Dados do Atendimento');
            $table->string('atend_tipo', 30)->nullable()->comment('Tipo de Atendimento');
            $table->string('ind_acid', 60)->nullable()->comment('Indicação Acidente/Doença');
            $table->string('cid10', 10)->nullable()->comment('CID-10');
            $table->text('obs')->nullable()->comment('Observação/Justificativa');
            $table->string('exec_contr_nome', 120)->nullable()->comment('Contratado Executante');
            $table->string('exec_contr_cod_op', 30)->nullable()->comment('Código Executante (Operadora)');
            $table->string('exec_contr_cnes', 20)->nullable()->comment('CNES (Executante)');
            $table->string('exec_atend_tipo', 30)->nullable()->comment('Tipo Atendimento (Exec.)');
            $table->string('exec_ind_acid', 60)->nullable()->comment('Indicação Acidente/Doença (Exec.)');
            $table->string('cons_tipo', 30)->nullable()->comment('Tipo de Consulta');
            $table->string('encerr_mot', 120)->nullable()->comment('Motivo de Encerramento');
            $table->text('exec_dados')->nullable()->comment('Dados Execução/Procedimento');
            $table->date('proc_dt')->nullable()->comment('Data');
            $table->time('hr_ini')->nullable()->comment('Hora Inicial');
            $table->time('hr_fim')->nullable()->comment('Hora Final');
            $table->string('proc_cod', 30)->nullable()->comment('Código do Procedimento');
            $table->string('proc_desc', 255)->nullable()->comment('Descrição do Procedimento');
            $table->unsignedInteger('qtd')->default(1)->comment('Quantidade');
            $table->string('via', 60)->nullable()->comment('Via de Acesso');
            $table->string('tecnica', 120)->nullable()->comment('Técnica Utilizada');
            $table->decimal('fator', 10, 2)->nullable()->comment('Fator Redutor/Acréscimo');
            $table->decimal('vl_unit', 10, 2)->nullable()->comment('Valor Unitário (R$)');
            $table->decimal('vl_total', 10, 2)->nullable()->comment('Valor Total (R$)');
            $table->string('grau', 30)->nullable()->comment('Grau de Participação');
            $table->string('exec_prof_cod', 30)->nullable()->comment('Cód. Profissional Executante');
            $table->string('exec_prof_nome', 120)->nullable()->comment('Nome Profissional Executante');
            $table->string('exec_prof_cnes', 20)->nullable()->comment('CNES (Prof. Executante)');
            $table->string('exec_prof_cons', 30)->nullable()->comment('Conselho (Prof. Executante)');
            $table->string('exec_prof_cons_num', 30)->nullable()->comment('Nº Conselho (Prof. Executante)');
            $table->char('exec_prof_uf', 2)->nullable()->comment('UF (Prof. Executante)');
            $table->string('exec_prof_cbo', 10)->nullable()->comment('CBO (Prof. Executante)');
            $table->date('serie_dt')->nullable()->comment('Data (Proced. em Série)');
            $table->text('serie_ass_benef')->nullable()->comment('Assinatura Benef./Resp. (Série)');
            $table->text('serie_obs')->nullable()->comment('Obs/Justificativa (Série)');
            $table->decimal('tot_proc', 10, 2)->nullable()->comment('Total Procedimentos (R$)');
            $table->decimal('tot_taxa', 10, 2)->nullable()->comment('Total Taxas/Aluguéis (R$)');
            $table->decimal('tot_mat', 10, 2)->nullable()->comment('Total Materiais (R$)');
            $table->decimal('tot_opme', 10, 2)->nullable()->comment('Total OPME (R$)');
            $table->decimal('tot_med', 10, 2)->nullable()->comment('Total Medicamentos (R$)');
            $table->decimal('tot_gas', 10, 2)->nullable()->comment('Total Gases (R$)');
            $table->decimal('tot_geral', 10, 2)->nullable()->comment('Total Geral (R$)');
            $table->text('ass_resp_aut')->nullable()->comment('Assinatura Resp. Autorização');
            $table->text('ass_benef')->nullable()->comment('Assinatura Benef./Resp.');
            $table->text('ass_contr')->nullable()->comment('Assinatura Contratado');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guias');
    }
};
