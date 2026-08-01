<div align="center">
    <p>
        <img src="https://raw.githubusercontent.com/rogerionasc/wcode_clinic/main/public/images/web_code.svg" width="1020" />
    </p>
</div>


<div align="center">
    <p>
        <a href="#">
            <img src="https://img.shields.io/github/repo-size/rogerionasc/wcode?style=flat-square" alt="Repo Size" />
        </a>
        <a href="https://hub.docker.com/r/rogerionasc/gesoft/tags">
            <img src="https://img.shields.io/github/issues/rogerionasc/wcode?arch=amd64&sort=date&style=flat-square" alt="Issues" />
        </a>
        <a href="https://hub.docker.com/r/rogerionasc/gesoft/tags">
            <img src="https://img.shields.io/github/contributors/rogerionasc/wcode?arch=amd64&sort=date&style=flat-square" alt="Contributors" />
        </a>
        <a href="https://hub.docker.com/r/rogerionasc/gesoft/tags">
            <img src="https://img.shields.io/github/commit-activity/t/rogerionasc/wcode?arch=amd64&sort=date&style=flat-square" alt="Commits" />
        </a>
        <a href="https://hub.docker.com/r/rogerionasc/gesoft/tags">
            <img src="https://img.shields.io/github/last-commit/rogerionasc/wcode?arch=amd64&sort=date&style=flat-square" alt="Last Commit" />
        </a>
    </p>
</div>

<div align="center">
    <p>
        <a href="#sobre">Sobre</a> |
        <a href="#instalação">Instalação</a> |
        <a href="#funcionalidade">Funcionalidade</a> |
        <a href="#tecnologias">Tecnologia</a> |
        <a href="#créditos">Crédito</a> |
        <a href="#licença">Licença</a>
    </p>
</div>

# Sobre

O `WCode Clinic ERP` é uma solução avançada de Enterprise Resource Planning (ERP) projetada especificamente para otimizar a gestão completa de clínicas, proporcionando uma abordagem integrada e eficiente para todas as operações. Desenvolvido com a mais recente tecnologia, o WCode Clinic ERP oferece uma gama abrangente de funcionalidades para atender às necessidades específicas do setor de saúde.
O WCode Clinic ERP é mais do que um sistema de gestão; é uma parceria na excelência clínica. Ele capacita as clínicas a oferecerem cuidados de saúde de alta qualidade, ao mesmo tempo que simplifica as operações administrativas, impulsionando a eficiência e o sucesso sustentável. Transforme sua clínica com o WCode Clinic ERP - onde a inovação encontra a saúde.

# Instalação

Clonar projeto em uma pasta local:
```sh
git clone https://github.com/rogerionasc/wcode.git wcode
```

Entrar no diretório:

```sh
cd wcode
```

Instalar dependências PHP:

```sh
composer install
```

Instalar dependências NPM:

```sh
npm install
```

Criar uma copia do arquivo .env-exemple:

```sh
cp .env.example .env
```

Gerar a chave da aplicação Laravel:

```sh
php artisan key:generate
```

Executar as migrations no banco de dados:

```sh
php artisan migrate
```

Executar as seeder's no banco de dados:

```sh
php artisan db:seed
```

Buildar o código:

```sh
npm run dev
```

Subir aplicação Laravel (http://localhost:8000/):

```sh
php artisan serve
```

Usuário padrão:

- **Username:** 
- **Password:** 


# Funcionalidade

<table>
    <tr>
        <th>Característica</th>
        <th>Descrição</th>
    </tr>
    <tr>
        <td>
            <p>Cadastro de Pacientes:</p>
        </td>
        <td>
             <p>Gerenciamento completo de informações dos pacientes, incluindo dados pessoais, histórico médico, contatos e registros financeiros.</p>
        </td>
    </tr>
    <tr>
        <td>
            <p>Agendamento de Consultas:</p>
        </td>
        <td>
             <p>Funcionalidade para agendar e gerenciar consultas médicas, exames e procedimentos.</p></p>
        </td>
    </tr>
    <tr>
        <td>
            <p>Prontuário Eletrônico:</p>
        </td>
        <td>
             <p>Armazenamento seguro e acessível eletronicamente de registros médicos, facilitando o acompanhamento do histórico de saúde dos pacientes.</p></p>
        </td>
    </tr>
    <tr>
        <td>
            <p>Prescrição Eletrônica:</p>
        </td>
        <td>
             <p>Possibilidade de prescrever medicamentos de forma eletrônica, com integração a farmácias e controle de estoque.</p>
        </td>
    </tr>
    <tr>
        <td>
            <p>Controle de Estoque:</p>
        </td>
        <td>
             <p>Gestão eficiente do estoque de medicamentos, materiais e equipamentos médicos, evitando desperdícios e garantindo disponibilidade quando necessário.</p>
        </td>
    </tr>
    <tr>
        <td>
            <p>Faturamento e Financeiro:</p>
        </td>
        <td>
             <p>Funcionalidades para emissão de faturas, controle de pagamentos, integração com convênios médicos e gestão de receitas e despesas.</p>
        </td>
    </tr>
    <tr>
        <td>
            <p>Gestão de Recursos Humanos:</p>
        </td>
        <td>
             <p>Controle de informações dos profissionais de saúde, escalas de trabalho, folha de pagamento e gestão de treinamentos.</p>
        </td>
    </tr>
    <tr>
        <td>
            <p>Integração Laboratorial:</p>
        </td>
        <td>
             <p>Possibilidade de prescrever medicamentos de forma eletrônica, com integração a farmácias e controle de estoque.</p>
        </td>
    </tr>
    <tr>
        <td>
            <p>Relatórios e Indicadores:</p>
        </td>
        <td>
             <p>Geração de relatórios gerenciais que oferecem insights sobre o desempenho operacional, financeiro e clínico da instituição.</p>
        </td>
    </tr>
    <tr>
        <td>
            <p>Comunicação Interna:</p>
        </td>
        <td>
             <p>Ferramentas para comunicação interna eficiente entre os membros da equipe médica e administrativa.</p>
        </td>
    </tr>
    <tr>
        <td>
            <p>Acesso Remoto:</p>
        </td>
        <td>
             <p>Acesso seguro às informações do sistema de qualquer lugar, facilitando a colaboração e o acompanhamento remoto.</p>
        </td>
    </tr>
   
    
</table>

## 💾 Modelo do Banco de Dados

```mermaid
---
config:
  look: neo
  theme: neo-dark
  layout: elk
---
erDiagram
    direction TB
    AGENDA_MEDICA {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED pessoa_id "" FK ""
        TINYINT UNSIGNED dia_semana "" ""
        TIME hora_inicio "" ""
        TIME hora_fim "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    AGENDAMENTOS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED agenda_medica_id "" FK ""
        DATE data "" ""
        TIME hora "" ""
        BIGINT UNSIGNED paciente_id "" FK ""
        BIGINT UNSIGNED procedimento_id "" FK ""
        BIGINT UNSIGNED tuss_id "" FK ""
        BIGINT UNSIGNED orcamento_id "" FK ""
        BIGINT UNSIGNED sessao_tratamento_id "" FK ""
        BIGINT UNSIGNED status_id "" FK ""
        BIGINT UNSIGNED agendamento_origem_id "" FK ""
        DECIMAL valor_cobrado "" ""
        TEXT observacoes "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    ATENDIMENTOS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED paciente_id "" FK ""
        BIGINT UNSIGNED convenio_id "" FK ""
        BIGINT UNSIGNED medico_id "" FK ""
        BIGINT UNSIGNED agendamento_id "" FK ""
        BIGINT UNSIGNED autorizacao_id "" FK ""
        BIGINT UNSIGNED guia_id "" FK ""
        BIGINT UNSIGNED caixa_pagamento_id "" FK ""
        BIGINT UNSIGNED procedimento_id "" FK ""
        BIGINT UNSIGNED categoria_procedimento_id "" FK ""
        VARCHAR tipo_atendimento "" ""
        DATE data_atendimento "" ""
        DATETIME hora_prevista "" ""
        DATETIME hora_inicio "" ""
        DATETIME hora_fim "" ""
        VARCHAR prioridade "" ""
        VARCHAR status "" ""
        TEXT observacao "" ""
        TEXT motivo_cancelamento "" ""
        BIGINT UNSIGNED criado_por "" ""
        BIGINT UNSIGNED atualizado_por "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    AUTORIZACOES {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED convenio_id "" FK ""
        VARCHAR carteira "" ""
        VARCHAR numero_autorizacao "" ""
        ENUM status "" ""
        DATE validade "" ""
        TIMESTAMP data_solicitacao "" ""
        TIMESTAMP data_resposta "" ""
        TEXT observacao "" ""
        BIGINT UNSIGNED usuario_id "" FK ""
        BIGINT UNSIGNED usuario_id_validou "" FK ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    CAIXAS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR descricao "" ""
        VARCHAR tipo "" ""
        TINYINT bloquear_receber "" ""
        TINYINT bloquear_pagar "" ""
        VARCHAR link_display "" ""
        TINYINT ativo "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    CANAIS_AVISO {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR nome "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    CATEGORIAS_PROCEDIMENTO {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR nome "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    CIDS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR codigo "" ""
        TEXT descricao "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    CONTAS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR nome "" ""
        VARCHAR cnpj "" ""
        VARCHAR pix_chave "" ""
        VARCHAR pix_nome "" ""
        VARCHAR pix_cidade "" ""
        VARCHAR pix_descricao "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    CONTAS_RECEBER {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED faturamento_id "" FK ""
        BIGINT UNSIGNED paciente_id "" FK ""
        BIGINT UNSIGNED convenio_id "" FK ""
        DECIMAL valor "" ""
        DATE vencimento "" ""
        VARCHAR status "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    CONVENIO_MEDICO_TUSS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED convenio_id "" FK ""
        BIGINT UNSIGNED pessoa_id "" FK ""
        BIGINT UNSIGNED tuss_id "" FK ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    CONVENIO_TUSS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED convenio_id "" FK ""
        BIGINT UNSIGNED tuss_id "" FK ""
        TINYINT requer_autorizacao "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    CONVENIOS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR descricao "" ""
        VARCHAR logo_path "" ""
        VARCHAR tuss_tabela "" ""
        VARCHAR tipo "" ""
        BIGINT UNSIGNED empresa_id "" FK ""
        INT UNSIGNED ans "" ""
        INT UNSIGNED dias_recebimento "" ""
        INT UNSIGNED dias_retorno "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    DOCUMENTOS_PRONTUARIO {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED prontuario_id "" FK ""
        BIGINT UNSIGNED modelo_documento_id "" FK ""
        BIGINT UNSIGNED pessoa_id "" FK ""
        DATETIME data_emissao "" ""
        TEXT conteudo_final "" ""
        TINYINT assinado "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    ENDERECOS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR cep "" ""
        VARCHAR endereco "" ""
        VARCHAR numero "" ""
        VARCHAR bairro "" ""
        VARCHAR cidade "" ""
        VARCHAR complemento "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    ESPECIALIDADE_PROCEDIMENTO {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED especialidade_id "" FK ""
        BIGINT UNSIGNED procedimento_id "" FK ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    ESPECIALIDADES {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR nome "" ""
        VARCHAR codigo "" ""
        TEXT descricao "" ""
        TINYINT ativo "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    ESTADO_CIVIL {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR descricao "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    FATURAMENTOS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED paciente_id "" FK ""
        BIGINT UNSIGNED agendamento_id "" FK ""
        VARCHAR tipo_pagador "" ""
        BIGINT UNSIGNED convenio_id "" FK ""
        DECIMAL valor_total "" ""
        DECIMAL valor_final "" ""
        DECIMAL valor_cobrado "" ""
        DECIMAL valor_aprovado "" ""
        DECIMAL valor_glosado "" ""
        VARCHAR status "" ""
        DATETIME data_faturamento "" ""
        DATE vencimento "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    GUIAS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED faturamento_id "" FK ""
        VARCHAR ans_registro "" ""
        VARCHAR numero_guia_prestador "" ""
        VARCHAR numero_guia_principal "" ""
        DATE data_autorizacao "" ""
        VARCHAR senha "" ""
        DATE data_validade_senha "" ""
        VARCHAR numero_guia_operadora "" ""
        VARCHAR numero_carteira "" ""
        DATE validade_carteira "" ""
        VARCHAR beneficiario_nome "" ""
        VARCHAR cns "" ""
        TINYINT atendimento_rn "" ""
        VARCHAR contratado_solicitante_codigo "" ""
        VARCHAR contratado_solicitante_nome "" ""
        VARCHAR profissional_solicitante_nome "" ""
        VARCHAR conselho_solicitante "" ""
        VARCHAR numero_conselho_solicitante "" ""
        VARCHAR uf_conselho_solicitante "" ""
        VARCHAR cbo_solicitante "" ""
        TEXT assinatura_profissional_solicitante "" ""
        VARCHAR carater_atendimento "" ""
        DATE data_solicitacao "" ""
        TEXT indicacao_clinica "" ""
        VARCHAR tabela_procedimento_solicitado "" ""
        VARCHAR procedimento_solicitado_codigo "" ""
        VARCHAR procedimento_solicitado_descricao "" ""
        TINYINT UNSIGNED quantidade_solicitada "" ""
        TINYINT UNSIGNED quantidade_autorizada "" ""
        VARCHAR contratado_executante_codigo "" ""
        VARCHAR contratado_executante_nome "" ""
        VARCHAR cnes_executante "" ""
        VARCHAR tipo_atendimento "" ""
        VARCHAR indicacao_acidente "" ""
        VARCHAR tipo_consulta "" ""
        VARCHAR motivo_encerramento "" ""
        DATE data_realizacao "" ""
        TIME hora_inicial "" ""
        TIME hora_final "" ""
        VARCHAR tabela_procedimento_realizado "" ""
        VARCHAR procedimento_realizado_codigo "" ""
        VARCHAR procedimento_realizado_descricao "" ""
        TINYINT UNSIGNED quantidade_realizada "" ""
        VARCHAR via_acesso "" ""
        VARCHAR tecnica_utilizada "" ""
        DECIMAL fator_reducao_acrescimo "" ""
        DECIMAL valor_unitario "" ""
        DECIMAL valor_total "" ""
        VARCHAR sequencial_referencia "" ""
        VARCHAR grau_participacao "" ""
        VARCHAR profissional_executante_codigo "" ""
        VARCHAR profissional_executante_nome "" ""
        VARCHAR conselho_executante "" ""
        VARCHAR numero_conselho_executante "" ""
        VARCHAR uf_conselho_executante "" ""
        VARCHAR cbo_executante "" ""
        DATE data_realizacao_serie "" ""
        TEXT assinatura_beneficiario_serie "" ""
        TEXT observacao_justificativa "" ""
        DECIMAL total_procedimentos "" ""
        DECIMAL total_taxas_alugueis "" ""
        DECIMAL total_materiais "" ""
        DECIMAL total_opme "" ""
        DECIMAL total_medicamentos "" ""
        DECIMAL total_gases_medicinais "" ""
        DECIMAL valor_total_geral "" ""
        TEXT assinatura_responsavel_autorizacao "" ""
        TEXT assinatura_beneficiario "" ""
        TEXT assinatura_contratado "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    GUICHES {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR nome "" ""
        VARCHAR hostname "" ""
        TINYINT status "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    HISTORICO_PRONTUARIO {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED atendimento_id "" FK ""
        DATETIME data_registro "" ""
        TEXT descricao "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    MODEL_HAS_PERMISSIONS {
        BIGINT UNSIGNED permission_id PK "" ""
        VARCHAR model_type PK "" ""
        BIGINT UNSIGNED model_id PK "" ""
        TIMESTAMP deleted_at "" ""
    }

    MODEL_HAS_ROLES {
        BIGINT UNSIGNED role_id PK "" ""
        VARCHAR model_type PK "" ""
        BIGINT UNSIGNED model_id PK "" ""
        TIMESTAMP deleted_at "" ""
    }

    MODELOS_DOCUMENTOS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR tipo "" ""
        VARCHAR nome "" ""
        TEXT conteudo_template "" ""
        TINYINT ativo "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    MOVIMENTACOES_CAIXA {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED caixa_id "" FK ""
        BIGINT UNSIGNED aberto_por_id "" FK ""
        VARCHAR numero "" ""
        DATETIME data_movimento "" ""
        DECIMAL total_entradas "" ""
        DECIMAL total_saidas "" ""
        DECIMAL saldo_caixa "" ""
        DECIMAL total_entrada_prazo "" ""
        DECIMAL total_saida_prazo "" ""
        DECIMAL total_transferencia "" ""
        DECIMAL total_conferencia "" ""
        DECIMAL saldo_movimento "" ""
        DECIMAL valor_diferenca "" ""
        DATETIME fechado_em "" ""
        BIGINT UNSIGNED fechado_por_id "" FK ""
        BIGINT UNSIGNED reaberto_por_id "" FK ""
        TEXT observacoes_fechamento "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    ORCAMENTO_PROCEDIMENTOS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED orcamento_id "" FK ""
        BIGINT UNSIGNED procedimento_id "" FK ""
        BIGINT UNSIGNED tuss_id "" FK ""
        INT UNSIGNED quantidade "" ""
        DECIMAL valor_unitario "" ""
        DECIMAL valor_total "" ""
        TEXT observacoes "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    ORCAMENTOS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR numero "" ""
        DATETIME data_emissao "" ""
        DATETIME validade "" ""
        BIGINT UNSIGNED convenio_id "" FK ""
        BIGINT UNSIGNED paciente_id "" FK ""
        DECIMAL valor_bruto "" ""
        DECIMAL desconto "" ""
        DECIMAL valor_total "" ""
        DECIMAL valor_avista "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    PACIENTE_CONVENIO {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED paciente_id "" FK ""
        BIGINT UNSIGNED convenio_id "" FK ""
        VARCHAR numero_carteira "" ""
        VARCHAR plano "" ""
        DATE validade "" ""
        TINYINT ativo "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    PACIENTE_RESPONSAVEL {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED paciente_id "" FK ""
        BIGINT UNSIGNED responsavel_id "" FK ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    PACIENTES {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR nome "" ""
        VARCHAR cpf "" ""
        VARCHAR cns "" ""
        VARCHAR rg "" ""
        VARCHAR sexo "" ""
        DATE data_nascimento "" ""
        VARCHAR naturalidade "" ""
        BIGINT UNSIGNED estado_civil_id "" FK ""
        DECIMAL altura "" ""
        DECIMAL peso "" ""
        VARCHAR cor_pele "" ""
        BIGINT UNSIGNED endereco_id "" FK ""
        TINYINT receber_avisos "" ""
        VARCHAR celular "" ""
        VARCHAR telefone "" ""
        VARCHAR email "" ""
        BIGINT UNSIGNED canal_aviso_id "" FK ""
        VARCHAR profissao "" ""
        VARCHAR escolaridade "" ""
        VARCHAR nome_mae "" ""
        VARCHAR nome_pai "" ""
        BIGINT UNSIGNED tipo_sanguineo_id "" FK ""
        TEXT observacoes "" ""
        TINYINT tem_responsavel "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    PAGAMENTOS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR nu_pagamento "" ""
        BIGINT UNSIGNED faturamento_id "" FK ""
        BIGINT UNSIGNED caixa_id "" FK ""
        BIGINT UNSIGNED movimentacao_id "" FK ""
        DECIMAL valor "" ""
        VARCHAR forma_pagamento "" ""
        DATETIME data_pagamento "" ""
        VARCHAR status "" ""
        TEXT recusa_justificativa "" ""
        BIGINT UNSIGNED recusado_por "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PAINEIS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR nome "" ""
        TINYINT status "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PARENTESCOS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR descricao "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    PEP_ANAMNESES {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED pep_id "" FK ""
        TEXT queixa_principal "" ""
        TEXT historia_doenca_atual "" ""
        TEXT antecedentes_pessoais "" ""
        TEXT antecedentes_familiares "" ""
        TEXT historico_social "" ""
        TEXT alergias "" ""
        TEXT medicamentos_uso "" ""
        TEXT habitos_vida "" ""
        TEXT observacao "" ""
        BIGINT UNSIGNED created_by "" ""
        BIGINT UNSIGNED updated_by "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PEP_ARQUIVOS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED pep_id "" FK ""
        VARCHAR nome "" ""
        VARCHAR arquivo "" ""
        VARCHAR mime_type "" ""
        INT tamanho "" ""
        TEXT observacao "" ""
        VARCHAR enviado_por "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PEP_ASSINATURAS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED pep_id "" FK ""
        BIGINT UNSIGNED documento_id "" FK ""
        BIGINT UNSIGNED profissional_id "" FK ""
        VARCHAR tipo_documento "" ""
        TEXT hash_documento "" ""
        TEXT certificado "" ""
        DATETIME assinado_em "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PEP_ATESTADOS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED pep_id "" FK ""
        INT dias "" ""
        BIGINT UNSIGNED cid_id "" FK ""
        TEXT texto "" ""
        DATETIME emitido_em "" ""
        BIGINT UNSIGNED profissional_id "" FK ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PEP_DIAGNOSTICOS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED pep_id "" FK ""
        BIGINT UNSIGNED cid_id "" FK ""
        TINYINT principal "" ""
        TEXT descricao "" ""
        TINYINT confirmado "" ""
        BIGINT UNSIGNED profissional_id "" FK ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PEP_DOCUMENTOS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED pep_id "" FK ""
        VARCHAR tipo "" ""
        VARCHAR titulo "" ""
        LONGTEXT conteudo "" ""
        DATETIME emitido_em "" ""
        BIGINT UNSIGNED profissional_id "" FK ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PEP_ENCAMINHAMENTOS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED pep_id "" FK ""
        VARCHAR especialidade_destino "" ""
        VARCHAR profissional_destino "" ""
        TEXT motivo "" ""
        TEXT observacao "" ""
        BIGINT UNSIGNED profissional_id "" FK ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PEP_EVOLUCOES {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED pep_id "" FK ""
        BIGINT UNSIGNED profissional_id "" FK ""
        VARCHAR tipo "" ""
        TEXT descricao "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PEP_EXAMES_FISICOS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED pep_id "" FK ""
        TEXT descricao "" ""
        TEXT observacao "" ""
        BIGINT UNSIGNED profissional_id "" FK ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PEP_PRESCRICAO_ITENS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED prescricao_id "" FK ""
        BIGINT UNSIGNED medicamento_id "" FK ""
        VARCHAR dosagem "" ""
        VARCHAR frequencia "" ""
        VARCHAR via "" ""
        VARCHAR duracao "" ""
        INT quantidade "" ""
        TEXT observacao "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PEP_PRESCRICOES {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED pep_id "" FK ""
        BIGINT UNSIGNED profissional_id "" FK ""
        TEXT observacao "" ""
        DATE validade "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PEP_PROCEDIMENTOS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED pep_id "" FK ""
        BIGINT UNSIGNED procedimento_id "" FK ""
        INT quantidade "" ""
        TEXT observacao "" ""
        BIGINT UNSIGNED profissional_id "" FK ""
        DATETIME realizado_em "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PEP_RECEITAS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED pep_id "" FK ""
        BIGINT UNSIGNED prescricao_id "" FK ""
        TEXT texto "" ""
        DATETIME emitido_em "" ""
        BIGINT UNSIGNED profissional_id "" FK ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PEP_SINAIS_VITAIS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED pep_id "" FK ""
        VARCHAR pressao_sistolica "" ""
        VARCHAR pressao_diastolica "" ""
        VARCHAR frequencia_cardiaca "" ""
        VARCHAR frequencia_respiratoria "" ""
        VARCHAR temperatura "" ""
        VARCHAR saturacao "" ""
        DECIMAL peso "" ""
        DECIMAL altura "" ""
        DECIMAL imc "" ""
        VARCHAR glicemia "" ""
        VARCHAR circunferencia_abdominal "" ""
        TEXT observacao "" ""
        BIGINT UNSIGNED profissional_id "" FK ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PEP_SOLICITACOES_EXAMES {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED pep_id "" FK ""
        BIGINT UNSIGNED procedimento_id "" FK ""
        TEXT justificativa "" ""
        TINYINT urgente "" ""
        VARCHAR status "" ""
        BIGINT UNSIGNED profissional_id "" FK ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PEPS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED atendimento_id "" FK ""
        BIGINT UNSIGNED paciente_id "" FK ""
        BIGINT UNSIGNED profissional_id "" FK ""
        DATETIME aberto_em "" ""
        DATETIME encerrado_em "" ""
        VARCHAR status "" ""
        TEXT observacao "" ""
        BIGINT UNSIGNED created_by "" ""
        BIGINT UNSIGNED updated_by "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    PERMISSIONS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR name "" ""
        VARCHAR guard_name "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    PESSOAS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR nome "" ""
        VARCHAR cpf "" ""
        VARCHAR rg "" ""
        VARCHAR sexo "" ""
        DATE data_nascimento "" ""
        VARCHAR naturalidade "" ""
        BIGINT UNSIGNED estado_civil_id "" FK ""
        VARCHAR cnes "" ""
        VARCHAR cargo "" ""
        VARCHAR crm "" ""
        BIGINT UNSIGNED endereco_id "" FK ""
        VARCHAR celular "" ""
        VARCHAR telefone "" ""
        VARCHAR email "" ""
        TEXT observacoes "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    PRE_CADASTRO {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR nome "" ""
        VARCHAR sobrenome "" ""
        VARCHAR cpf "" ""
        VARCHAR telefone "" ""
        DATE data_nascimento "" ""
        VARCHAR email "" ""
        VARCHAR password "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    PRESCRICOES {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED prontuario_id "" FK ""
        BIGINT UNSIGNED pessoa_id "" FK ""
        DATETIME data_prescricao "" ""
        TEXT prescricao "" ""
        TEXT observacoes "" ""
        TINYINT ativa "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    PROCEDIMENTOS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR nome "" ""
        VARCHAR descricao "" ""
        BIGINT UNSIGNED categoria_id "" FK ""
        TINYINT eh_tratamento "" ""
        INT UNSIGNED quantidade_sessoes "" ""
        DECIMAL valor "" ""
        DECIMAL comissao_percentual "" ""
        TINYINT ativo "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    PROFISSIONAL_ESPECIALIDADE {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED pessoa_id "" FK ""
        BIGINT UNSIGNED especialidade_id "" FK ""
        VARCHAR qre "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    PRONTUARIOS {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED paciente_id "" FK ""
        VARCHAR codigo "" ""
        DATE data_abertura "" ""
        TINYINT ativo "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    RESPONSAVEIS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR nome "" ""
        BIGINT UNSIGNED parentesco_id "" FK ""
        VARCHAR cpf "" ""
        VARCHAR rg "" ""
        DATE data_nascimento "" ""
        BIGINT UNSIGNED endereco_id "" FK ""
        VARCHAR celular "" ""
        VARCHAR telefone "" ""
        VARCHAR email "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    ROLE_HAS_PERMISSIONS {
        BIGINT UNSIGNED permission_id PK "" ""
        BIGINT UNSIGNED role_id PK "" ""
        TIMESTAMP deleted_at "" ""
    }

    ROLES {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR name "" ""
        VARCHAR guard_name "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    SALAS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR nome "" ""
        TINYINT status "" ""
        BIGINT UNSIGNED pessoa_id "" FK ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    SESSIONS {
        VARCHAR id PK "" ""
        BIGINT UNSIGNED user_id "" FK ""
        VARCHAR ip_address "" ""
        TEXT user_agent "" ""
        LONGTEXT payload "" ""
        INT last_activity "" ""
        TIMESTAMP deleted_at "" ""
    }

    SESSOES_TRATAMENTO {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED procedimento_id "" FK ""
        BIGINT UNSIGNED tuss_id "" FK ""
        BIGINT UNSIGNED paciente_id "" FK ""
        INT UNSIGNED numero_sessao "" ""
        DATE data_prevista "" ""
        TINYINT realizada "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    SOLICITACAO_EXAMES {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED prontuario_id "" FK ""
        BIGINT UNSIGNED pessoa_id "" FK ""
        TEXT prescricao "" ""
        TEXT observacoes "" ""
        TINYINT ativa "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    STATUS_AGENDAMENTO {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR descricao "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    TIPO_SANGUINEO {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR descricao "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    TOTEM_OPCOES {
        BIGINT UNSIGNED id PK "" ""
        BIGINT UNSIGNED totem_id "" FK ""
        VARCHAR nome "" ""
        VARCHAR codigo "" ""
        TINYINT status "" ""
        VARCHAR icone "" ""
        VARCHAR cor "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    TOTENS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR nome "" ""
        TINYINT status "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
    }

    TUSS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR tabela "" ""
        VARCHAR codigo "" ""
        VARCHAR descricao "" ""
        DECIMAL m2_filme "" ""
        DECIMAL auxiliares "" ""
        DECIMAL incidencia "" ""
        VARCHAR porte "" ""
        DECIMAL ch "" ""
        DECIMAL co "" ""
        DECIMAL total "" ""
        TINYINT eh_tratamento "" ""
        INT UNSIGNED quantidade_sessoes "" ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }

    USERS {
        BIGINT UNSIGNED id PK "" ""
        VARCHAR email "" ""
        VARCHAR password "" ""
        TINYINT is_active "" ""
        TEXT two_factor_secret "" ""
        TEXT two_factor_recovery_codes "" ""
        TIMESTAMP two_factor_confirmed_at "" ""
        BIGINT UNSIGNED pessoa_id "" FK ""
        TIMESTAMP email_verified_at "" ""
        VARCHAR remember_token "" ""
        BIGINT UNSIGNED current_team_id "" FK ""
        VARCHAR profile_photo_path "" ""
        BIGINT UNSIGNED conta_id "" FK ""
        TIMESTAMP created_at "" ""
        TIMESTAMP updated_at "" ""
        TIMESTAMP deleted_at "" ""
    }


	CONTAS||--o{USUARIOS:"possui"
	ENDERECOS||--o{PACIENTES:"reside_em"
	ENDERECOS||--o{RESPONSAVEIS:"reside_em"
	ENDERECOS||--o{PROFISSIONAIS_SAUDE:"reside_em"
	TIPO_SANGUINEO||--o{PACIENTES:"possui"
	CANAIS_AVISO||--o{PACIENTES:"utiliza"
	PACIENTES||--||PRONTUARIOS:"gera"
	PACIENTES||--o{PACIENTE_CONVENIO:"possui"
	CONVENIO||--o{PACIENTE_CONVENIO:"vincula"
	PARENTESCOS||--o{RESPONSAVEIS:"define"
	PACIENTES||--o{PACIENTE_RESPONSAVEL:"possui"
	RESPONSAVEIS||--o{PACIENTE_RESPONSAVEL:"vincula"
	CATEGORIAS_PROCEDIMENTO||--o{PROCEDIMENTOS:"classifica"
	ESPECIALIDADES||--o{PROCEDIMENTOS:"classifica"
	CONVENIO||--o{PROCEDIMENTO_CONVENIO:"possui"
	PROCEDIMENTOS||--o{PROCEDIMENTO_CONVENIO:"vincula"
	PROCEDIMENTOS||--o{SESSOES_TRATAMENTO:"gera"
	PACIENTES||--o{SESSOES_TRATAMENTO:"realiza"
	SESSOES_TRATAMENTO||--||AGENDAMENTOS:"gera"
	PROFISSIONAIS_SAUDE||--o{AGENDA_MEDICA:"define"
	AGENDA_MEDICA||--o{AGENDAMENTOS:"permite"
	PACIENTES||--o{AGENDAMENTOS:"agenda"
	PROCEDIMENTOS||--o{AGENDAMENTOS:"refere"
	STATUS_AGENDAMENTO||--o{AGENDAMENTOS:"status"
	ORCAMENTOS||--o{AGENDAMENTOS:"vincula"
	AGENDAMENTOS||--||ATENDIMENTOS:"gera"
	PRONTUARIOS||--o{ATENDIMENTOS:"registra"
	PROFISSIONAIS_SAUDE||--o{ATENDIMENTOS:"realiza"
	ESPECIALIDADES||--o{ATENDIMENTOS:"atua"
	PROFISSIONAIS_SAUDE||--o{PROFISSIONAL_ESPECIALIDADE:"possui"
	ESPECIALIDADES||--o{PROFISSIONAL_ESPECIALIDADE:"classifica"
	ATENDIMENTOS||--o{HISTORICO_PRONTUARIO:"registra"
	PRONTUARIOS||--o{DOCUMENTOS_PRONTUARIO:"possui"
	MODELOS_DOCUMENTOS||--o{DOCUMENTOS_PRONTUARIO:"origina"
	PROFISSIONAIS_SAUDE||--o{DOCUMENTOS_PRONTUARIO:"emite"
	PRONTUARIOS||--o{PRESCRICOES:"possui"
	PROFISSIONAIS_SAUDE||--o{PRESCRICOES:"prescreve"
	PRONTUARIOS||--o{SOLICITACAO_EXAMES:"possui"
	PROFISSIONAIS_SAUDE||--o{SOLICITACAO_EXAMES:"solicita"
	PACIENTES||--o{ORCAMENTOS:"solicita"
	CONVENIO||--o{ORCAMENTOS:"aplica"
	ORCAMENTOS||--o{ORCAMENTO_PROCEDIMENTOS:"possui"
	PROCEDIMENTOS||--o{ORCAMENTO_PROCEDIMENTOS:"compoe"
	ORCAMENTOS||--o{PAGAMENTOS:"recebe"
	ORCAMENTOS||--||FATURAMENTOS:"gera"
	FATURAMENTOS||--o{PAGAMENTOS:"recebe"
	FATURAMENTOS||--o{CONTAS_RECEBER:"cobra"
	PACIENTES||--o{FATURAMENTOS:"gera"
	PACIENTES||--o{CONTAS_RECEBER:"deve"
    CAIXAS ||--o{ PAGAMENTOS : "recebe"
	CAIXAS ||--o{ MOVIMENTACOES_CAIXA : "fecha"
	MOVIMENTACOES_CAIXA ||--o{ PAGAMENTOS : "lança"
    MOVIMENTACOES_CAIXA ||--o{ CONFERENCIAS : "possui"
	CAIXAS ||--o{ CONFERENCIAS : "é_conferido"
	USUARIOS||--o{PAGAMENTOS:"recusa"
	USUARIOS||--o{MOVIMENTACOES_CAIXA:"abre"
	USUARIOS||--o{MOVIMENTACOES_CAIXA:"fecha"
	USUARIOS||--o{MOVIMENTACOES_CAIXA:"reabre"
```

# Licença

## Licença do WCode Clinic - Versão 1.0

Este é um contrato legal entre o licenciado a WCode Solução e Inovação LTDA. Ao utilizar o software fornecido por WCode Solução e Inovação LTDA, o Usuário concorda com os termos e condições deste contrato.

1. Concessão de Licença:

A WCode Solução e Inovação LTDA concede ao Usuário uma licença não exclusiva e intransferível para usar o Software de acordo com os termos e condições estabelecidos neste contrato, em conformidade com o artigo 7º, inciso VI, da Lei nº 9.610/1998 (Lei de Direitos Autorais).

2. Restrições:

a. O Usuário concorda em não reproduzir, modificar, distribuir ou sublicenciar o Software, total ou parcialmente, sem a permissão expressa por escrito da WCode Solução e Inovação LTDA, conforme previsto no artigo 29, incisos I e III, da Lei nº 9.610/1998.

b. O Usuário concorda em não realizar engenharia reversa, descompilar ou desmontar o Software, exceto na medida permitida por lei, de acordo com o artigo 6º, inciso IV, da Lei nº 9.609/1998 (Lei de Software).

3. Propriedade Intelectual:

Todos os direitos de propriedade intelectual relacionados ao Software são de propriedade exclusiva da WCode Solução e Inovação LTDA, em conformidade com o artigo 8º da Lei nº 9.610/1998. Nada neste contrato concede ao Usuário quaisquer direitos de propriedade intelectual sobre o Software, exceto os expressamente concedidos neste documento.

4. Suporte Técnico:

A WCode Solução e Inovação LTDA pode, a seu critério exclusivo, fornecer suporte técnico para o Software. Tal suporte será regido por termos separados.

5. Isenção de Garantias:

O Software é fornecido "como está", sem garantias de qualquer tipo, expressas ou implícitas, incluindo, mas não se limitando a, garantias de comerciabilidade, adequação a uma finalidade específica e não infração, conforme estabelecido no artigo 26, inciso II, do Código de Defesa do Consumidor.

6. Limitação de Responsabilidade:

Em nenhuma circunstância a WCode Solução e Inovação LTDA será responsável por quaisquer danos diretos, indiretos, incidentais, especiais, exemplares ou consequenciais, incluindo perda de lucros, interrupção de negócios ou perda de dados, de acordo com o artigo 14 do Código de Defesa do Consumidor.

7. Lei Aplicável e Jurisdição:

Este contrato é regido pelas leis da República Federativa do Brasil. As partes concordam que qualquer disputa decorrente deste contrato será resolvida pelos tribunais competentes na jurisdição da sede da WCode Solução e Inovação LTDA.

Ao utilizar o Software, o Usuário concorda com os termos e condições deste contrato, em conformidade com a legislação brasileira vigente.
