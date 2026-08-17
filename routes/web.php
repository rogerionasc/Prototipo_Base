<?php

use App\Http\Controllers\VelzonRoutesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\ConvenioController;
use App\Http\Controllers\AutorizacaoController;
use App\Http\Controllers\EspecialidadeController;
use App\Http\Controllers\ProcedimentoController;
use App\Http\Controllers\AgendaMedicaController;
use App\Http\Controllers\OrcamentoController;
use App\Http\Controllers\CaixaController;
use App\Http\Controllers\MovimentacaoCaixaController;
use App\Http\Controllers\AgendamentoController;
use App\Http\Controllers\FaturamentoController;
use App\Http\Controllers\ContasMedicasController;
use App\Http\Controllers\ContasReceberController;
use App\Http\Controllers\AtendimentoController;
use App\Http\Controllers\PepController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\GuicheController;
use App\Http\Controllers\TotemController;
use App\Http\Controllers\TotemOpcaoController;
use App\Http\Controllers\PainelController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TipoAtendimentoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified',])->group(function () {

    Route::get('/usuarios/pessoas-disponiveis', [UserController::class, 'pessoasDisponiveis'])->name('usuarios.pessoas_disponiveis');
    Route::put('/usuarios/{usuario}/toggle-status', [UserController::class, 'toggleStatus'])->name('usuarios.toggle_status');
    Route::resource('usuarios', UserController::class)->names('usuarios');
    Route::get('/atendimentos', [AtendimentoController::class, 'index'])->name('atendimentos.index');
    Route::post('/atendimentos/{atendimento}/chamar', [AtendimentoController::class, 'chamar'])->name('atendimentos.chamar');
    Route::post('/atendimentos/{atendimento}/iniciar', [AtendimentoController::class, 'iniciar'])->name('atendimentos.iniciar');
    Route::post('/atendimentos/{atendimento}/ausente', [AtendimentoController::class, 'ausente'])->name('atendimentos.ausente');
    Route::post('/atendimentos/{atendimento}/finalizar', [AtendimentoController::class, 'finalizar'])->name('atendimentos.finalizar');
    Route::get('/atendimentos/{atendimento}/pep', [PepController::class, 'show'])->name('atendimentos.pep');
    Route::post('/atendimentos/{atendimento}/pep/anamnese', [PepController::class, 'saveAnamnese'])->name('atendimentos.pep.anamnese.save');
    Route::post('/atendimentos/{atendimento}/pep/sinais-vitais', [PepController::class, 'saveSinaisVitais'])->name('atendimentos.pep.sinais-vitais.save');
    Route::post('/atendimentos/{atendimento}/pep/evolucao', [PepController::class, 'saveEvolucao'])->name('atendimentos.pep.evolucao.save');
    Route::delete('/atendimentos/{atendimento}/pep/evolucao/{evolucao}', [PepController::class, 'deleteEvolucao'])->name('atendimentos.pep.evolucao.delete');


    Route::delete('/atendimentos/{atendimento}/pep/tratamento/{tratamento}', [PepController::class, 'deleteTratamento'])->name('atendimentos.pep.tratamento.delete');

    Route::post('/atendimentos/{atendimento}/pep/prescricao', [PepController::class, 'savePrescricao'])->name('atendimentos.pep.prescricao.save');
    Route::delete('/atendimentos/{atendimento}/pep/prescricao/{prescricao}', [PepController::class, 'deletePrescricao'])->name('atendimentos.pep.prescricao.delete');

    Route::post('/atendimentos/{atendimento}/pep/diagnostico', [PepController::class, 'storeDiagnostico'])->name('atendimentos.pep.diagnostico.save');
    Route::get('/cids/search', [\App\Http\Controllers\CidController::class, 'search'])->name('cids.search');
    Route::get('/cids/list', [\App\Http\Controllers\CidController::class, 'list'])->name('cids.list');
    Route::get('/cids/template', [\App\Http\Controllers\CidController::class, 'template'])->name('cids.template');
    Route::post('/cids/import/progress', [\App\Http\Controllers\CidController::class, 'importCidsProgress'])->name('cids.import.progress');

    Route::controller(VelzonRoutesController::class)->group(function () {

        // dashboards
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('medicos', 'medico');
        Route::get('empregados', 'empregados');
        Route::get('componentes', 'componentes')->name('componentes');
        Route::get('configuracao', 'configuracao')->name('configuracao.index');
        Route::get('configuracao/parametrizacao', 'configuracaoParametrizacao')->name('configuracao.parametrizacao');
        Route::get('configuracao/parametrizacao/tiss', 'configuracaoTiss')->name('configuracao.tiss');
        Route::get('configuracao/especialidades', 'configuracaoEspecialidades')->name('configuracao.especialidades');
        Route::get('configuracao/tuss', 'configuracaoTuss')->name('configuracao.tuss');
        Route::get('configuracao/cid', 'configuracaoCid')->name('configuracao.cid');
        Route::get('configuracao/procedimentos', 'configuracaoProcedimentos')->name('configuracao.procedimentos');

        // Pacientes routes
        Route::get("/pacientes", [PacienteController::class, "index"]);
        Route::get("/pacientes/search", [PacienteController::class, "search"])->name('pacientes.search');
        Route::get("/pacientes/{id}", [PacienteController::class, "show"])->whereNumber('id')->name('pacientes.show');
        Route::post("/pacientes", [PacienteController::class, "store"])->name('pacientes.store');
        Route::put("/pacientes/{id}", [PacienteController::class, "update"])->name('pacientes.update');
        Route::delete("/pacientes/{id}", [PacienteController::class, "destroy"])->name('pacientes.destroy');
        Route::delete("/pacientes/bulk", [PacienteController::class, "destroyMany"])->name('pacientes.destroy_many');
        Route::get("/pacientes/{id}/convenios", [PacienteController::class, "convenios"])->whereNumber('id')->name('pacientes.convenios');
        Route::get("/pacientes/{id}/agendamentos", [\App\Http\Controllers\AgendamentoController::class, "byPaciente"])->whereNumber('id')->name('agendamentos.by_paciente');

        // Convênios routes
        Route::get("/convenios", [ConvenioController::class, "index"])->name('convenios.index');
        Route::post("/convenios", [ConvenioController::class, "store"])->name('convenios.store');
        Route::put("/convenios/{id}", [ConvenioController::class, "update"])->name('convenios.update');
        Route::delete("/convenios/{id}", [ConvenioController::class, "destroy"])->name('convenios.destroy');
        Route::get("/convenios/{id}/tuss-procedimentos", [ConvenioController::class, "tussProcedimentos"])->whereNumber('id')->name('convenios.tuss_procedimentos');
        Route::get("/convenios/{id}/procedimentos-orcamento", [ConvenioController::class, "procedimentosOrcamento"])->whereNumber('id')->name('convenios.procedimentos_orcamento');

        // Autorizações routes
        Route::get("/convenios/autorizacoes", [AutorizacaoController::class, "index"])->name('autorizacoes.index');
        Route::post("/convenios/autorizacoes", [AutorizacaoController::class, "store"])->name('autorizacoes.store');
        Route::put("/convenios/autorizacoes/{id}", [AutorizacaoController::class, "update"])->name('autorizacoes.update');
        Route::delete("/convenios/autorizacoes/{id}", [AutorizacaoController::class, "destroy"])->name('autorizacoes.destroy');

        // Caixas routes
        Route::get("/cadastro-caixa", [CaixaController::class, "create"])->name('caixas.create');
        Route::post("/caixas", [CaixaController::class, "store"])->name('caixas.store');
        Route::put("/caixas/{id}", [CaixaController::class, "update"])->name('caixas.update');
        Route::delete("/caixas/{id}", [CaixaController::class, "destroy"])->name('caixas.destroy');

        // Movimentações de Caixa routes
        Route::get("/movimentacoes-caixa/pendentes", [MovimentacaoCaixaController::class, "pendentes"])->name('movimentacoes_caixa.pendentes');
        Route::get("/movimentacoes-caixa", [MovimentacaoCaixaController::class, "index"])->name('movimentacoes_caixa.index');
        Route::post("/movimentacoes-caixa", [MovimentacaoCaixaController::class, "store"])->name('movimentacoes_caixa.store');
        Route::get("/movimentacoes-caixa/{id}", [MovimentacaoCaixaController::class, "show"])->whereNumber('id')->name('movimentacoes_caixa.show');
        Route::put("/movimentacoes-caixa/{id}", [MovimentacaoCaixaController::class, "update"])->name('movimentacoes_caixa.update');
        Route::delete("/movimentacoes-caixa/{id}", [MovimentacaoCaixaController::class, "destroy"])->name('movimentacoes_caixa.destroy');
        Route::put("/movimentacoes-caixa/{id}/reopen", [MovimentacaoCaixaController::class, "reopen"])->name('movimentacoes_caixa.reopen');

        // PIX config
        Route::get("/config/pix", [\App\Http\Controllers\PixConfigController::class, "show"]);
        Route::put("/config/pix", [\App\Http\Controllers\PixConfigController::class, "update"]);
        // Especialidades Médicas routes
        Route::post("/especialidades", [EspecialidadeController::class, "store"])->name('especialidades.store');
        Route::put("/especialidades/{id}", [EspecialidadeController::class, "update"])->name('especialidades.update');
        Route::delete("/especialidades/{id}", [EspecialidadeController::class, "destroy"])->name('especialidades.destroy');

        // Procedimentos routes
        Route::post("/procedimentos", [ProcedimentoController::class, "store"])->name('procedimentos.store');
        Route::put("/procedimentos/{id}", [ProcedimentoController::class, "update"])->name('procedimentos.update');
        Route::delete("/procedimentos/{id}", [ProcedimentoController::class, "destroy"])->name('procedimentos.destroy');
        Route::post("/tuss", [ProcedimentoController::class, "storeTuss"])->name('tuss.store');
        Route::put("/tuss/{id}", [ProcedimentoController::class, "updateTuss"])->name('tuss.update');
        Route::get("/tuss/list", [ProcedimentoController::class, "listTuss"])->name('tuss.list');
        Route::post("/tuss/import", [ProcedimentoController::class, "importTuss"])->name('tuss.import');
        Route::post("/tuss/import/start", [ProcedimentoController::class, "startTussImport"])->name('tuss.import.start');
        Route::get("/tuss/import/status/{id}", [ProcedimentoController::class, "tussImportStatus"])->name('tuss.import.status');
        Route::get("/tuss/import/complete/{id}", [ProcedimentoController::class, "completeTussImport"])->name('tuss.import.complete');
        Route::post("/tuss/import/progress", [ProcedimentoController::class, "importTussProgress"])->name('tuss.import.progress');
        Route::get("/tuss/template", [ProcedimentoController::class, "downloadTussTemplate"])->name('tuss.template');
        Route::get("/tuss/tabelas/{tabela}/procedimentos", [ProcedimentoController::class, "tussProcedimentosByTabela"])->name('tuss.tabela.procedimentos');

        // Orçamentos routes
        Route::get("/orcamentos", [OrcamentoController::class, "index"])->name('orcamentos.index');
        Route::post("/orcamentos", [OrcamentoController::class, "store"])->name('orcamentos.store');
        Route::get("/orcamentos/{id}", [OrcamentoController::class, "show"])->whereNumber('id')->name('orcamentos.show');
        Route::put("/orcamentos/{id}", [OrcamentoController::class, "update"])->whereNumber('id')->name('orcamentos.update');
        Route::get("/orcamentos/search", [OrcamentoController::class, "search"])->name('orcamentos.search');
        Route::get("/orcamentos/search-paid", [OrcamentoController::class, "searchPaid"])->name('orcamentos.search_paid');
        Route::get("/orcamentos/{id}/print", [OrcamentoController::class, "print"])->whereNumber('id')->name('orcamentos.print');
        Route::get("/pacientes/{id}/orcamentos", [OrcamentoController::class, "byPaciente"])->whereNumber('id')->name('orcamentos.by_paciente');
        Route::put("/orcamentos/{id}/approve", [OrcamentoController::class, "approve"])->whereNumber('id')->name('orcamentos.approve');
        Route::put("/orcamentos/{id}/unapprove", [OrcamentoController::class, "unapprove"])->whereNumber('id')->name('orcamentos.unapprove');

        // Contas Médicas
        Route::get("/contas-medicas/validacao-guias", [ContasMedicasController::class, "validacaoGuias"])->name('contas_medicas.validacao_guias');
        Route::post("/contas-medicas/validacao-guias/encaminhar", [ContasMedicasController::class, "encaminharFaturamento"])->name('contas_medicas.encaminhar_faturamento');

        // Faturamento
        Route::get("/faturamento/particular", [FaturamentoController::class, "particular"])->name('faturamento.particular');
        Route::get("/faturamento/convenios", [FaturamentoController::class, "convenios"])->name('faturamento.convenios');
        Route::get("/faturamento/guias-disponiveis", [FaturamentoController::class, "getGuiasDisponiveis"])->name('faturamento.guias_disponiveis');
        Route::post("/faturamento/lote", [FaturamentoController::class, "storeLote"])->name('faturamento.store_lote');
        Route::get("/faturamentos/{id}/guias", [FaturamentoController::class, "getGuiasLote"])->whereNumber('id')->name('faturamentos.guias');
        Route::post("/faturamentos/{id}/guias", [FaturamentoController::class, "addGuiasLote"])->whereNumber('id')->name('faturamentos.guias.add');
        Route::delete("/faturamentos/{lote}/guias/{guia}", [FaturamentoController::class, "removeGuiaLote"])->whereNumber('lote')->whereNumber('guia')->name('faturamentos.guias.remove');
        Route::patch("/faturamentos/{lote}/guias/{guia}/status", [FaturamentoController::class, "updateGuiaStatus"])->whereNumber('lote')->whereNumber('guia')->name('faturamentos.guias.updateStatus');
        Route::patch("/faturamentos/{lote}/guias/{guia}/glosa", [FaturamentoController::class, "updateGuiaGlosa"])->whereNumber('lote')->whereNumber('guia')->name('faturamentos.guias.updateGlosa');
        Route::put("/faturamentos/{id}/convenio", [FaturamentoController::class, "updateConvenio"])->whereNumber('id')->name('faturamentos.convenio.update');
        Route::post("/faturamento/guias/{id}/devolver", [FaturamentoController::class, "devolverGuia"])->whereNumber('id')->name('faturamento.guias.devolver');

        // Financeiro
        // Route::put("/faturamentos/{id}/convenio", [FaturamentoController::class, "updateConvenio"])->whereNumber('id')->name('faturamentos.convenio.update');
        Route::get("/faturamentos/{id}/detalhes", [FaturamentoController::class, "detalhes"])->whereNumber('id')->name('faturamentos.detalhes');
        Route::get("/contas-receber", [ContasReceberController::class, "index"])->name('financeiro.contas_receber.index');
        Route::post("/faturamentos/{id}/receber-financeiro", [ContasReceberController::class, "receiveConvenio"])->whereNumber('id')->name('financeiro.receber_convenio');

        // Agenda Médica routes
        Route::post("/agenda-medica", [AgendaMedicaController::class, "store"])->name('agenda_medica.store');
        Route::get("/agendamentos/profissionais-por-procedimento", [AgendamentoController::class, "profissionaisPorProcedimento"])->name('agendamentos.profissionais_por_procedimento');
        Route::get("/agendamentos", [AgendamentoController::class, "index"])->name('agendamentos.index');

        // Fila da Recepção
        Route::get("/recepcao/fila", [\App\Http\Controllers\RecepcaoFilaController::class, "index"])->name('recepcao.fila.index');
        Route::post("/recepcao/fila/{agendamento}/confirmar", [\App\Http\Controllers\RecepcaoFilaController::class, "confirmar"])->name('recepcao.fila.confirmar');
        Route::post("/recepcao/fila/{agendamento}/cancelar", [\App\Http\Controllers\RecepcaoFilaController::class, "cancelar"])->name('recepcao.fila.cancelar');
        Route::post("/recepcao/fila/{agendamento}/emergencia", [\App\Http\Controllers\RecepcaoFilaController::class, "toggleEmergencia"])->name('recepcao.fila.emergencia');

        Route::get("/agendamentos/{id}", [AgendamentoController::class, "show"])->whereNumber('id')->name('agendamentos.show');
        Route::post("/agendamentos", [AgendamentoController::class, "store"])->name('agendamentos.store');
        Route::put("/agendamentos/{id}", [AgendamentoController::class, "update"])->whereNumber('id')->name('agendamentos.update');
        Route::put("/agendamentos/{id}/reschedule-session", [AgendamentoController::class, "rescheduleSession"])->whereNumber('id')->name('agendamentos.reschedule_session');
        Route::get("/agendamentos/latest", [AgendamentoController::class, "latest"])->name('agendamentos.latest');
        Route::get("/agendas-medicas/by-date", [AgendamentoController::class, "agendasByDate"])->name('agendas_medicas.by_date');
        Route::get("/agendas-medicas/counts-by-weekday", [AgendamentoController::class, "countsByWeekday"])->name('agendas_medicas.counts_by_weekday');
        Route::get("/agendas-medicas/weekday-by-doctors", [AgendamentoController::class, "weekdayByDoctors"])->name('agendas_medicas.weekday_by_doctors');
        Route::put("/agendamentos/{id}/cancel", [AgendamentoController::class, "cancel"])->whereNumber('id')->name('agendamentos.cancel');

        // Pagamentos
        Route::post("/faturamentos/{id}/pagamentos", [\App\Http\Controllers\PagamentoController::class, "startForFaturamento"])->whereNumber('id')->name('faturamentos.pagamentos.start');
        Route::put("/pagamentos/{id}/confirm", [\App\Http\Controllers\PagamentoController::class, "confirm"])->whereNumber('id')->name('pagamentos.confirm');
        Route::put("/pagamentos/{id}/refuse", [\App\Http\Controllers\PagamentoController::class, "refuse"])->whereNumber('id')->name('pagamentos.refuse');
        Route::put("/pagamentos/{id}/unrefuse", [\App\Http\Controllers\PagamentoController::class, "unrefuse"])->whereNumber('id')->name('pagamentos.unrefuse');
        Route::get("/pagamentos-recusados", [\App\Http\Controllers\PagamentoController::class, "recusados"])->name('pagamentos.recusados');
        // PIX display
        Route::get("/pix/display", [\App\Http\Controllers\PagamentoController::class, "displayPix"])->name('pix.display');
        Route::get("/pix/current", [\App\Http\Controllers\PagamentoController::class, "currentPix"])->name('pix.current');
        Route::put("/pagamentos/{id}/prepare-pix", [\App\Http\Controllers\PagamentoController::class, "preparePix"])->whereNumber('id')->name('pagamentos.prepare_pix');
        Route::put("/pagamentos/{id}/cancel-pix", [\App\Http\Controllers\PagamentoController::class, "cancelPix"])->whereNumber('id')->name('pagamentos.cancel_pix');
        Route::post("/pix/mp/checkout", [\App\Http\Controllers\PagamentoController::class, "mpCheckout"])->name('pix.mp.checkout');
        Route::post("/pix/mp/status-check", [\App\Http\Controllers\PagamentoController::class, "mpStatusCheck"])->name('pix.mp.status_check');
        Route::get("/agenda-medica/{id}", [AgendaMedicaController::class, "showByProfissional"])->name('agenda_medica.show_by_prof');
        Route::delete("/agenda-medica/{id}", [AgendaMedicaController::class, "destroy"])->name('agenda_medica.destroy');

        // Profissionais de Saúde routes
        Route::post("/profissionais-saude", [\App\Http\Controllers\PessoaController::class, "store"])->name('pessoas.store');
        Route::put("/profissionais-saude/{id}", [\App\Http\Controllers\PessoaController::class, "update"])->name('pessoas.update');
        Route::delete("/profissionais-saude/{id}", [\App\Http\Controllers\PessoaController::class, "destroy"])->name('pessoas.destroy');

        // Parametrização routes
        Route::post("/parametros/estado-civil", [VelzonRoutesController::class, "parametros_store_estado_civil"])->name('parametros.estado_civil.store');
        Route::post("/parametros/tipo-sanguineo", [VelzonRoutesController::class, "parametros_store_tipo_sanguineo"])->name('parametros.tipo_sanguineo.store');
        Route::put("/parametros/estado-civil/{id}", [VelzonRoutesController::class, "parametros_update_estado_civil"])->name('parametros.estado_civil.update');
        Route::delete("/parametros/estado-civil/{id}", [VelzonRoutesController::class, "parametros_destroy_estado_civil"])->name('parametros.estado_civil.destroy');
        Route::post("/parametros/canal-aviso", [VelzonRoutesController::class, "parametros_store_canal_aviso"])->name('parametros.canal_aviso.store');
        Route::put("/parametros/canal-aviso/{id}", [VelzonRoutesController::class, "parametros_update_canal_aviso"])->name('parametros.canal_aviso.update');
        Route::delete("/parametros/canal-aviso/{id}", [VelzonRoutesController::class, "parametros_destroy_canal_aviso"])->name('parametros.canal_aviso.destroy');
        Route::post("/parametros/parentesco", [VelzonRoutesController::class, "parametros_store_parentesco"])->name('parametros.parentesco.store');
        Route::put("/parametros/parentesco/{id}", [VelzonRoutesController::class, "parametros_update_parentesco"])->name('parametros.parentesco.update');
        Route::delete("/parametros/parentesco/{id}", [VelzonRoutesController::class, "parametros_destroy_parentesco"])->name('parametros.parentesco.destroy');
        Route::post("/parametros/categoria-procedimento", [VelzonRoutesController::class, "parametros_store_categoria_procedimento"])->name('parametros.categoria_procedimento.store');
        Route::put("/parametros/categoria-procedimento/{id}", [VelzonRoutesController::class, "parametros_update_categoria_procedimento"])->name('parametros.categoria_procedimento.update');
        Route::delete("/parametros/categoria-procedimento/{id}", [VelzonRoutesController::class, "parametros_destroy_categoria_procedimento"])->name('parametros.categoria_procedimento.destroy');

        Route::post("/parametros/comorbidade", [VelzonRoutesController::class, "parametros_store_comorbidade"])->name('parametros.comorbidade.store');
        Route::put("/parametros/comorbidade/{id}", [VelzonRoutesController::class, "parametros_update_comorbidade"])->name('parametros.comorbidade.update');
        Route::delete("/parametros/comorbidade/{id}", [VelzonRoutesController::class, "parametros_destroy_comorbidade"])->name('parametros.comorbidade.destroy');
        Route::post("/parametros/conselho", [VelzonRoutesController::class, "parametros_store_conselho"])->name('parametros.conselho.store');
        Route::put("/parametros/conselho/{id}", [VelzonRoutesController::class, "parametros_update_conselho"])->name('parametros.conselho.update');
        Route::delete("/parametros/conselho/{id}", [VelzonRoutesController::class, "parametros_destroy_conselho"])->name('parametros.conselho.destroy');

        Route::post("/parametros/carater-atendimento", [VelzonRoutesController::class, "parametros_store_carater_atendimento"])->name('parametros.carater_atendimento.store');
        Route::put("/parametros/carater-atendimento/{id}", [VelzonRoutesController::class, "parametros_update_carater_atendimento"])->name('parametros.carater_atendimento.update');
        Route::delete("/parametros/carater-atendimento/{id}", [VelzonRoutesController::class, "parametros_destroy_carater_atendimento"])->name('parametros.carater_atendimento.destroy');

        Route::post("/parametros/tabela-referencia", [VelzonRoutesController::class, "parametros_store_tabela_referencia"])->name('parametros.tabela_referencia.store');
        Route::put("/parametros/tabela-referencia/{id}", [VelzonRoutesController::class, "parametros_update_tabela_referencia"])->name('parametros.tabela_referencia.update');
        Route::delete("/parametros/tabela-referencia/{id}", [VelzonRoutesController::class, "parametros_destroy_tabela_referencia"])->name('parametros.tabela_referencia.destroy');
        Route::put("/parametros/tipo-sanguineo/{id}", [VelzonRoutesController::class, "parametros_update_tipo_sanguineo"])->name('parametros.tipo_sanguineo.update');
        Route::delete("/parametros/tipo-sanguineo/{id}", [VelzonRoutesController::class, "parametros_destroy_tipo_sanguineo"])->name('parametros.tipo_sanguineo.destroy');
        Route::post("/parametros/canal-aviso", [VelzonRoutesController::class, "parametros_store_canal_aviso"])->name('parametros.canal_aviso.store');
        Route::put("/parametros/canal-aviso/{id}", [VelzonRoutesController::class, "parametros_update_canal_aviso"])->name('parametros.canal_aviso.update');
        Route::delete("/parametros/canal-aviso/{id}", [VelzonRoutesController::class, "parametros_destroy_canal_aviso"])->name('parametros.canal_aviso.destroy');
        Route::post("/parametros/parentesco", [VelzonRoutesController::class, "parametros_store_parentesco"])->name('parametros.parentesco.store');
        Route::put("/parametros/parentesco/{id}", [VelzonRoutesController::class, "parametros_update_parentesco"])->name('parametros.parentesco.update');
        Route::delete("/parametros/parentesco/{id}", [VelzonRoutesController::class, "parametros_destroy_parentesco"])->name('parametros.parentesco.destroy');
        Route::post("/parametros/tipo-atendimento", [TipoAtendimentoController::class, "parametros_store_tipo_atendimento"])->name('parametros.tipo_atendimento.store');
        Route::put("/parametros/tipo-atendimento/{id}", [TipoAtendimentoController::class, "parametros_update_tipo_atendimento"])->name('parametros.tipo_atendimento.update');
        Route::delete("/parametros/tipo-atendimento/{id}", [TipoAtendimentoController::class, "parametros_destroy_tipo_atendimento"])->name('parametros.tipo_atendimento.destroy');

        Route::post("/parametros/indicacao-incidencia", [\App\Http\Controllers\IndicacaoIncidenciaController::class, "store"])->name('parametros.indicacao_incidencia.store');
        Route::put("/parametros/indicacao-incidencia/{id}", [\App\Http\Controllers\IndicacaoIncidenciaController::class, "update"])->name('parametros.indicacao_incidencia.update');
        Route::delete("/parametros/indicacao-incidencia/{id}", [\App\Http\Controllers\IndicacaoIncidenciaController::class, "destroy"])->name('parametros.indicacao_incidencia.destroy');

        Route::post("/parametros/tipo-consulta", [\App\Http\Controllers\TipoConsultaController::class, "store"])->name('parametros.tipo_consulta.store');
        Route::put("/parametros/tipo-consulta/{id}", [\App\Http\Controllers\TipoConsultaController::class, "update"])->name('parametros.tipo_consulta.update');
        Route::delete("/parametros/tipo-consulta/{id}", [\App\Http\Controllers\TipoConsultaController::class, "destroy"])->name('parametros.tipo_consulta.destroy');

        Route::post("/parametros/motivo-encerramento", [\App\Http\Controllers\MotivoEncerramentoController::class, "store"])->name('parametros.motivo_encerramento.store');
        Route::put("/parametros/motivo-encerramento/{id}", [\App\Http\Controllers\MotivoEncerramentoController::class, "update"])->name('parametros.motivo_encerramento.update');
        Route::delete("/parametros/motivo-encerramento/{id}", [\App\Http\Controllers\MotivoEncerramentoController::class, "destroy"])->name('parametros.motivo_encerramento.destroy');

        Route::post("/parametros/via-acesso", [\App\Http\Controllers\ViaAcessoController::class, "store"])->name('parametros.via_acesso.store');
        Route::put("/parametros/via-acesso/{id}", [\App\Http\Controllers\ViaAcessoController::class, "update"])->name('parametros.via_acesso.update');
        Route::delete("/parametros/via-acesso/{id}", [\App\Http\Controllers\ViaAcessoController::class, "destroy"])->name('parametros.via_acesso.destroy');

        Route::post("/parametros/tecnica-utilizada", [\App\Http\Controllers\TecnicaUtilizadaController::class, "store"])->name('parametros.tecnica_utilizada.store');
        Route::put("/parametros/tecnica-utilizada/{id}", [\App\Http\Controllers\TecnicaUtilizadaController::class, "update"])->name('parametros.tecnica_utilizada.update');
        Route::delete("/parametros/tecnica-utilizada/{id}", [\App\Http\Controllers\TecnicaUtilizadaController::class, "destroy"])->name('parametros.tecnica_utilizada.destroy');

        Route::post("/parametros/grau-participacao", [\App\Http\Controllers\GrauParticipacaoController::class, "store"])->name('parametros.grau_participacao.store');
        Route::put("/parametros/grau-participacao/{id}", [\App\Http\Controllers\GrauParticipacaoController::class, "update"])->name('parametros.grau_participacao.update');
        Route::delete("/parametros/grau-participacao/{id}", [\App\Http\Controllers\GrauParticipacaoController::class, "destroy"])->name('parametros.grau_participacao.destroy');
        // Salas
        Route::resource('clinica/salas', SalaController::class)->names('salas')->parameters(['salas' => 'sala'])->except(['create', 'show', 'edit']);
        Route::resource('clinica/guiches', GuicheController::class)->names('guiches')->parameters(['guiches' => 'guiche'])->except(['create', 'show', 'edit']);
        Route::resource('clinica/totens', TotemController::class)->names('totens')->parameters(['totens' => 'totem'])->except(['create', 'show', 'edit']);
        Route::post('clinica/totens/{totem}/opcoes/sync', [TotemOpcaoController::class, 'sync'])->name('totens.opcoes.sync');
        Route::resource('clinica/totens.opcoes', TotemOpcaoController::class)->names('totens.opcoes')->parameters(['totens' => 'totem', 'opcoes' => 'opcao'])->only(['store', 'update', 'destroy']);
        Route::resource('clinica/paineis', PainelController::class)->names('paineis')->parameters(['paineis' => 'painel'])->except(['create', 'show', 'edit']);
    });
});

// PIX webhook (sem autenticação)
Route::post('/pix/webhook', [\App\Http\Controllers\PagamentoController::class, 'pixWebhook'])->name('pix.webhook');
Route::post('/pix/mp/webhook', [\App\Http\Controllers\PagamentoController::class, 'mpWebhook'])->name('pix.mp.webhook');

// Web Apps
Route::get('/app/totem/{totem?}', function ($totem = null) {
    if (!$totem) {
        $totemModel = \App\Models\Totem::with(['opcoes' => function ($q) {
            $q->where('status', true);
        }])->where('status', true)->first();
    } else {
        $totemModel = \App\Models\Totem::with(['opcoes' => function ($q) {
            $q->where('status', true);
        }])->where('status', true)->findOrFail($totem);
    }

    return Inertia\Inertia::render('Apps/Totem', [
        'totem' => $totemModel
    ]);
})->name('apps.totem');

Route::get('/app/painel-senha', function () {
    return Inertia\Inertia::render('Apps/PainelSenha');
})->name('apps.painel_senha');

Route::get('/app/painel-atendimento', function () {
    return Inertia\Inertia::render('Apps/PainelAtendimento');
})->name('apps.painel_atendimento');

Route::get('/app/painel/data', function () {
    // Pega os últimos 5 atendimentos chamados do dia atual
    $atendimentos = \App\Models\Atendimento::with(['paciente', 'medico.salas'])
        ->whereIn('status', ['CHAMADO', 'EM ATENDIMENTO'])
        ->whereDate('updated_at', \Carbon\Carbon::today())
        ->orderBy('updated_at', 'desc')
        ->take(5)
        ->get()
        ->map(function ($atendimento) {
            $sala = $atendimento->medico->salas->first();
            return [
                'id' => $atendimento->id,
                'paciente' => $atendimento->paciente->nome,
                'medico' => $atendimento->medico->nome,
                'local' => $sala ? $sala->nome : 'Consultório',
                'updated_at' => $atendimento->updated_at
            ];
        });

    return response()->json($atendimentos);
})->name('apps.painel.data');

Route::get('/guias/{agendamento}/imprimir', [App\Http\Controllers\GuiaController::class, 'imprimirDaAgenda'])->name('guias.imprimirDaAgenda');
Route::get('/guias/{agendamento}/dados', [App\Http\Controllers\GuiaController::class, 'getDadosDaAgenda'])->name('guias.dados');
Route::put('/guias/{id}', [App\Http\Controllers\GuiaController::class, 'update'])->name('guias.update');
