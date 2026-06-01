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
	CONTAS {
		INT id PK ""  
		VARCHAR nome  ""  
		VARCHAR cnpj  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}
   
	CAIXAS {
		INT id PK
		VARCHAR descricao
		VARCHAR tipo
		BOOLEAN bloquear_receber
		BOOLEAN bloquear_pagar
		BOOLEAN ativo
		DATETIME created_at
		DATETIME updated_at
	}

	PAGAMENTOS {
		INT id PK
		INT orcamento_id FK
		INT caixa_id FK
		INT movimentacao_id FK
		DECIMAL valor
		VARCHAR forma_pagamento
		DATETIME data_pagamento
		BOOLEAN confirmado
		VARCHAR status
		TEXT recusa_justificativa
		INT recusado_por FK
		DATETIME created_at
		DATETIME updated_at
	}

	MOVIMENTACOES_CAIXA {
		INT id PK
		INT caixa_id FK
		INT aberto_por_id FK
		VARCHAR numero
		DATETIME data_movimento
		DECIMAL total_entradas
		DECIMAL total_saidas
		DECIMAL saldo_caixa
		DECIMAL total_entrada_prazo
		DECIMAL total_saida_prazo
		DECIMAL total_transferencia
		DECIMAL total_conferencia
		DECIMAL saldo_movimento
		DECIMAL valor_diferenca
		DATETIME fechado_em
		INT fechado_por_id FK
		INT reaberto_por_id FK
		TEXT observacoes_fechamento
		DATETIME created_at
		DATETIME updated_at
	}

	CONFERENCIAS {
		INT id PK
		INT movimentacao_caixa_id FK
		INT caixa_id FK
		INT usuario_id FK
		DECIMAL valor_credito
        DECIMAL valor_debito
        DECIMAL valor_pix
        DECIMAL valor_dinheiro
		DATETIME conferido_em
		DATETIME created_at
		DATETIME updated_at
	}

	USUARIOS {
		INT id PK ""  
		VARCHAR nome  ""  
		VARCHAR email  ""  
		VARCHAR senha_hash  ""  
		INT conta_id FK ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	ENDERECOS {
		INT id PK ""  
		VARCHAR cep  ""  
		VARCHAR endereco  ""  
		VARCHAR numero  ""  
		VARCHAR bairro  ""  
		VARCHAR cidade  ""  
		VARCHAR complemento  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	CONVENIO {
		INT id PK ""  
		VARCHAR descricao  ""  
		VARCHAR tipo  ""  
		VARCHAR empresa_id  ""  
		INT ans  ""  
		INT dias_recebimento  ""  
		INT dias_retorno  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	CATEGORIAS_PROCEDIMENTO {
		INT id PK ""  
		VARCHAR nome  ""  
		TEXT descricao  ""  
		BOOLEAN ativo  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	PROCEDIMENTOS {
		INT id PK ""  
		VARCHAR nome  ""  
		VARCHAR descricao  ""  
		INT categoria_id FK ""  
		INT especialidade_id FK ""  
		BOOLEAN eh_tratamento  ""  
		INT quantidade_sessoes  ""  
		DECIMAL valor  ""  
		DECIMAL comissao_percentual  ""  
		BOOLEAN ativo  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	PROCEDIMENTO_CONVENIO {
		INT id PK ""  
		INT procedimento_id FK ""  
		INT convenio_id FK ""  
		DECIMAL valor_convenio  ""  
		BOOLEAN ativo  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	PACIENTES {
		INT id PK ""  
		VARCHAR nome  ""  
		VARCHAR cpf  ""  
		VARCHAR rg  ""  
		VARCHAR sexo  ""  
		DATE data_nascimento  ""  
		VARCHAR naturalidade  ""  
		INT estado_civil_id FK ""  
		DECIMAL altura  ""  
		DECIMAL peso  ""  
		VARCHAR cor_pele  ""  
		INT endereco_id FK ""  
		BOOLEAN receber_avisos  ""  
		VARCHAR celular  ""  
		VARCHAR telefone  ""  
		VARCHAR email  ""  
		INT canal_aviso_id FK ""  
		VARCHAR profissao  ""  
		VARCHAR escolaridade  ""  
		VARCHAR nome_mae  ""  
		VARCHAR nome_pai  ""  
		INT tipo_sanguineo_id FK ""  
		TEXT observacoes  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	PACIENTE_CONVENIO {
		INT id PK ""  
		INT paciente_id FK ""  
		INT convenio_id FK ""  
		VARCHAR numero_carteira  ""  
		VARCHAR plano  ""  
		DATE validade  ""  
		BOOLEAN ativo  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	TIPO_SANGUINEO {
		INT id PK ""  
		VARCHAR descricao  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	CANAIS_AVISO {
		INT id PK ""  
		VARCHAR nome  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	PARENTESCOS {
		INT id PK ""  
		VARCHAR descricao  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	RESPONSAVEIS {
		INT id PK ""  
		VARCHAR nome  ""  
		INT parentesco_id FK ""  
		VARCHAR cpf  ""  
		VARCHAR rg  ""  
		DATE data_nascimento  ""  
		INT endereco_id FK ""  
		VARCHAR celular  ""  
		VARCHAR telefone  ""  
		VARCHAR email  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	PACIENTE_RESPONSAVEL {
		INT paciente_id FK ""  
		INT responsavel_id FK ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	PRONTUARIOS {
		INT id PK ""  
		INT paciente_id FK ""  
		VARCHAR codigo  ""  
		DATE data_abertura  ""  
		BOOLEAN ativo  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	ESPECIALIDADES {
		INT id PK ""  
		VARCHAR nome  ""  
		VARCHAR codigo  ""  
		TEXT descricao  ""  
		BOOLEAN ativo  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	PROFISSIONAIS_SAUDE {
		INT id PK ""  
		VARCHAR nome  ""  
		VARCHAR cpf  ""  
		VARCHAR rg  ""  
		VARCHAR sexo  ""  
		DATE data_nascimento  ""  
		VARCHAR naturalidade  ""  
		INT estado_civil_id FK ""  
		VARCHAR cnes  ""  
		VARCHAR crm  ""  
		INT endereco_id FK ""  
		VARCHAR celular  ""  
		VARCHAR telefone  ""  
		VARCHAR email  ""  
		TEXT observacoes  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	PROFISSIONAL_ESPECIALIDADE {
		INT profissional_saude_id FK ""  
		INT especialidade_id FK ""  
		VARCHAR qre  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	AGENDA_MEDICA {
		INT id PK ""  
		INT profissional_saude_id FK ""  
		INT dia_semana  ""  
		TIME hora_inicio  ""  
		TIME hora_fim  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	STATUS_AGENDAMENTO {
		INT id PK ""  
		VARCHAR descricao  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	SESSOES_TRATAMENTO {
		INT id PK ""  
		INT procedimento_id FK ""  
		INT paciente_id FK ""  
		INT numero_sessao  ""  
		DATE data_prevista  ""  
		BOOLEAN realizada  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	AGENDAMENTOS {
		INT id PK ""  
		INT agenda_medica_id FK ""  
		DATE data  ""  
		TIME hora  ""  
		INT paciente_id FK ""  
		INT procedimento_id FK ""  
		INT orcamento_id FK ""  
		INT sessao_tratamento_id FK ""  
		INT status_id FK ""  
		INT agendamento_origem_id FK ""  
		DECIMAL valor_cobrado  ""  
		TEXT observacoes  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	ATENDIMENTOS {
		INT id PK ""  
		INT agendamento_id FK ""  
		INT prontuario_id FK ""  
		INT profissional_saude_id FK ""  
		INT especialidade_id FK ""  
		DATE data  ""  
		DATETIME inicio_atendimento  ""  
		DATETIME fim_atendimento  ""  
		TEXT evolucao  ""  
		VARCHAR cid  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	HISTORICO_PRONTUARIO {
		INT id PK ""  
		INT atendimento_id FK ""  
		DATETIME data_registro  ""  
		TEXT descricao  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	MODELOS_DOCUMENTOS {
		INT id PK ""  
		VARCHAR tipo  ""  
		VARCHAR nome  ""  
		TEXT conteudo_template  ""  
		BOOLEAN ativo  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	DOCUMENTOS_PRONTUARIO {
		INT id PK ""  
		INT prontuario_id FK ""  
		INT modelo_documento_id FK ""  
		INT profissional_saude_id FK ""  
		DATETIME data_emissao  ""  
		TEXT conteudo_final  ""  
		BOOLEAN assinado  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	PRESCRICOES {
		INT id PK ""  
		INT prontuario_id FK ""  
		INT profissional_saude_id FK ""  
		DATETIME data_prescricao  ""  
		TEXT prescricao  ""  
		TEXT observacoes  ""  
		BOOLEAN ativa  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	SOLICITACAO_EXAMES {
		INT id PK ""  
		INT prontuario_id FK ""  
		INT profissional_saude_id FK ""  
		TEXT prescricao  ""  
		TEXT observacoes  ""  
		BOOLEAN ativa  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	ORCAMENTOS {
		INT id PK ""  
		VARCHAR numero  ""  
		DATE data_emissao  ""  
		DATE validade  ""  
		INT convenio_id FK ""  
		INT paciente_id FK ""  
		DECIMAL valor_bruto  ""  
		DECIMAL desconto  ""  
		DECIMAL valor_total  ""  
		DECIMAL valor_avista  ""  
		BOOLEAN faturamento_previsto  ""  
		BOOLEAN aprovado  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	ORCAMENTO_PROCEDIMENTOS {
		INT id PK ""  
		INT orcamento_id FK ""  
		INT procedimento_id FK ""  
		INT quantidade  ""  
		DECIMAL valor_unitario  ""  
		DECIMAL valor_total  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
	}

	FATURAMENTOS {
		INT id PK ""  
		INT pagamento_id FK ""  
		DATE data_faturamento  ""  
		DECIMAL valor_faturado  ""  
		VARCHAR status  ""  
		DATETIME created_at  ""  
		DATETIME updated_at  ""  
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
	PAGAMENTOS||--||FATURAMENTOS:"origina"
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
