<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParametrizacaoTissSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Caráter de Atendimento (Tabela 23)
        $carateres = [
            ['codigo' => '1', 'descricao' => 'Eletiva'],
            ['codigo' => '2', 'descricao' => 'Urgência/Emergência'],
        ];
        foreach ($carateres as $data) {
            \App\Models\CaraterAtendimento::updateOrCreate(['codigo' => $data['codigo']], $data);
        }

        // 2. Tabela de Referência (Tabela 62)
        $tabelas = [
            ['codigo' => '16', 'descricao' => 'TUSS - Procedimentos e Eventos em Saúde'],
            ['codigo' => '18', 'descricao' => 'TUSS - Taxas Diárias e Gases'],
            ['codigo' => '19', 'descricao' => 'TUSS - Materiais e OPME'],
            ['codigo' => '20', 'descricao' => 'TUSS - Medicamentos'],
            ['codigo' => '22', 'descricao' => 'Tabela Própria das Operadoras'],
            ['codigo' => '90', 'descricao' => 'Tabela Própria do Pacote'],
            ['codigo' => '98', 'descricao' => 'Tabela Própria de Pacote Odontológico'],
            ['codigo' => '99', 'descricao' => 'Outras Tabelas'],
            ['codigo' => '00', 'descricao' => 'Tabela Própria da Operadora - Materiais, Medicamentos, e OPME'],
        ];
        foreach ($tabelas as $data) {
            \App\Models\TabelaReferencia::updateOrCreate(['codigo' => $data['codigo']], $data);
        }

        // 3. Tipos de Atendimento (Tabela 50)
        $tiposAtendimento = [
            ['codigo' => '01', 'descricao' => 'Remoção'],
            ['codigo' => '02', 'descricao' => 'Pequena Cirurgia'],
            ['codigo' => '03', 'descricao' => 'Terapias'],
            ['codigo' => '04', 'descricao' => 'Consulta'],
            ['codigo' => '05', 'descricao' => 'Exames'],
            ['codigo' => '06', 'descricao' => 'Atendimento Domiciliar'],
            ['codigo' => '07', 'descricao' => 'Internação'],
            ['codigo' => '08', 'descricao' => 'Quimioterapia'],
            ['codigo' => '09', 'descricao' => 'Radioterapia'],
            ['codigo' => '10', 'descricao' => 'Terapia Renal Substitutiva (TRS)'],
            ['codigo' => '11', 'descricao' => 'Pronto Socorro'],
        ];
        foreach ($tiposAtendimento as $data) {
            \App\Models\TipoAtendimento::updateOrCreate(['codigo' => $data['codigo']], $data);
        }

        // 4. Indicação de Acidente (Tabela 36)
        $indicacoes = [
            ['codigo' => '0', 'descricao' => 'Acidente ou doença do trabalho'],
            ['codigo' => '1', 'descricao' => 'Acidente de trânsito'],
            ['codigo' => '2', 'descricao' => 'Outros acidentes'],
            ['codigo' => '9', 'descricao' => 'Não acidente'],
        ];
        foreach ($indicacoes as $data) {
            \App\Models\IndicacaoIncidencia::updateOrCreate(['codigo' => $data['codigo']], $data);
        }

        // 5. Tipo de Consulta (Tabela 52)
        $tiposConsulta = [
            ['codigo' => '1', 'descricao' => 'Primeira Consulta'],
            ['codigo' => '2', 'descricao' => 'Retorno'],
            ['codigo' => '3', 'descricao' => 'Pré-natal'],
            ['codigo' => '4', 'descricao' => 'Por encaminhamento'],
        ];
        foreach ($tiposConsulta as $data) {
            \App\Models\TipoConsulta::updateOrCreate(['codigo' => $data['codigo']], $data);
        }

        // 6. Via de Acesso (Tabela 60)
        $viasAcesso = [
            ['codigo' => '1', 'descricao' => 'Única'],
            ['codigo' => '2', 'descricao' => 'Mesma via'],
            ['codigo' => '3', 'descricao' => 'Vias diferentes'],
        ];
        foreach ($viasAcesso as $data) {
            if (class_exists(\App\Models\ViaAcesso::class)) {
                \App\Models\ViaAcesso::updateOrCreate(['codigo' => $data['codigo']], $data);
            }
        }

        // 7. Técnica Utilizada (Tabela 61)
        $tecnicas = [
            ['codigo' => '1', 'descricao' => 'Convencional'],
            ['codigo' => '2', 'descricao' => 'Vídeo'],
            ['codigo' => '3', 'descricao' => 'Robótica'],
        ];
        foreach ($tecnicas as $data) {
            if (class_exists(\App\Models\TecnicaUtilizada::class)) {
                \App\Models\TecnicaUtilizada::updateOrCreate(['codigo' => $data['codigo']], $data);
            }
        }

        // 8. Motivo Encerramento (Tabela 43)
        $motivos = [
            ['codigo' => '11', 'descricao' => 'Alta curado'],
            ['codigo' => '12', 'descricao' => 'Alta melhorado'],
            ['codigo' => '14', 'descricao' => 'Alta a pedido'],
            ['codigo' => '15', 'descricao' => 'Transferência para outro estabelecimento'],
            ['codigo' => '16', 'descricao' => 'Transferência para internação domiciliar'],
            ['codigo' => '18', 'descricao' => 'Alta por evasão'],
            ['codigo' => '21', 'descricao' => 'Óbito - com declaração de óbito fornecida pelo médico assistente'],
            ['codigo' => '22', 'descricao' => 'Óbito - com declaração de óbito fornecida pelo IML'],
            ['codigo' => '23', 'descricao' => 'Óbito - com declaração de óbito fornecida pelo SVO'],
            ['codigo' => '24', 'descricao' => 'Óbito - mulher em idade fértil'],
            ['codigo' => '25', 'descricao' => 'Óbito - nascituro'],
            ['codigo' => '26', 'descricao' => 'Óbito - recém-nascido'],
            ['codigo' => '27', 'descricao' => 'Outros motivos de saída'],
            ['codigo' => '28', 'descricao' => 'Permanência por características próprias da doença'],
            ['codigo' => '29', 'descricao' => 'Permanência por intercorrência clínica'],
            ['codigo' => '31', 'descricao' => 'Alta Hospitalar'],
            ['codigo' => '32', 'descricao' => 'Permanência'],
        ];
        foreach ($motivos as $data) {
            if (class_exists(\App\Models\MotivoEncerramento::class)) {
                \App\Models\MotivoEncerramento::updateOrCreate(['codigo' => $data['codigo']], $data);
            }
        }

        // 9. Grau de Participação (Tabela 24)
        $graus = [
            ['codigo' => '00', 'descricao' => 'Principal'],
            ['codigo' => '01', 'descricao' => 'Cirurgião Principal'],
            ['codigo' => '02', 'descricao' => 'Primeiro Auxiliar'],
            ['codigo' => '03', 'descricao' => 'Segundo Auxiliar'],
            ['codigo' => '04', 'descricao' => 'Terceiro Auxiliar'],
            ['codigo' => '05', 'descricao' => 'Quarto Auxiliar'],
            ['codigo' => '06', 'descricao' => 'Anestesista'],
            ['codigo' => '07', 'descricao' => 'Clínico'],
            ['codigo' => '08', 'descricao' => 'Pediátra'],
            ['codigo' => '09', 'descricao' => 'Intensivista'],
        ];
        foreach ($graus as $data) {
            if (class_exists(\App\Models\GrauParticipacao::class)) {
                \App\Models\GrauParticipacao::updateOrCreate(['codigo' => $data['codigo']], $data);
            }
        }
    }
}
