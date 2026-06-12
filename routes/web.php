<?php

use App\Http\Controllers\VelzonRoutesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\ConvenioController;
use App\Http\Controllers\EspecialidadeController;
use App\Http\Controllers\ProcedimentoController;
use App\Http\Controllers\AgendaMedicaController;
use App\Http\Controllers\OrcamentoController;
use App\Http\Controllers\CaixaController;
use App\Http\Controllers\MovimentacaoCaixaController;
use App\Http\Controllers\AgendamentoController;
use App\Http\Controllers\FaturamentoController;
use App\Http\Controllers\ContasReceberController;

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

    Route::controller(VelzonRoutesController::class)->group(function () {

        // dashboards
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('medicos', 'medico');
        Route::get('usuarios', 'usuario');
        Route::get('componentes', 'componentes')->name('componentes');
        Route::get('configuracao', 'configuracao')->name('configuracao.index');


        // pages routes
        Route::get("/pages/starter", "pages_starter");
        Route::get("/pages/maintenance", "pages_maintenance");
        Route::get("/pages/coming-soon", "pages_coming_soon");

        // auth sample page routes
        Route::get("/auth/signin-basic", "auth_signin_basic");
        Route::get("/auth/signin-cover", "auth_signin_cover");
        Route::get("/auth/signup-basic", "auth_signup_basic");
        Route::get("/auth/signup-cover", "auth_signup_cover");
        Route::get("/auth/reset-pwd-basic", "auth_reset_pwd_basic");
        Route::get("/auth/reset-pwd-cover", "auth_reset_pwd_cover");
        Route::get("/auth/create-pwd-basic", "auth_create_pwd_basic");
        Route::get("/auth/create-pwd-cover", "auth_create_pwd_cover");
        Route::get("/auth/lockscreen-basic", "auth_lockscreen_basic");
        Route::get("/auth/lockscreen-cover", "auth_lockscreen_cover");
        Route::get("/auth/twostep-basic", "auth_twostep_basic");
        Route::get("/auth/twostep-cover", "auth_twostep_cover");
        Route::get("/auth/404", "auth_404");
        Route::get("/auth/500", "auth_500");
        Route::get("/auth/404-basic", "auth_404_basic");
        Route::get("/auth/404-cover", "auth_404_cover");
        Route::get("/auth/ofline", "auth_ofline");
        Route::get("/auth/logout-basic", "auth_logout_basic");
        Route::get("/auth/logout-cover", "auth_logout_cover");
        Route::get("/auth/success-msg-basic", "auth_success_msg_basic");
        Route::get("/auth/success-msg-cover", "auth_success_msg_cover");

        // Test routes
        Route::controller(VelzonRoutesController::class)->group(function () {
            Route::get("/test-page", "test_func");
            // other routes
        });

        // Pacientes routes
        Route::get("/pacientes", [PacienteController::class, "index"]);
        Route::get("/pacientes/search", [PacienteController::class, "search"])->name('pacientes.search');
        Route::post("/pacientes", [PacienteController::class, "store"])->name('pacientes.store');
        Route::put("/pacientes/{id}", [PacienteController::class, "update"])->name('pacientes.update');
        Route::delete("/pacientes/{id}", [PacienteController::class, "destroy"])->name('pacientes.destroy');
        Route::delete("/pacientes/bulk", [PacienteController::class, "destroyMany"])->name('pacientes.destroy_many');
        Route::get("/pacientes/{id}/convenios", [PacienteController::class, "convenios"])->whereNumber('id')->name('pacientes.convenios');

        // Convênios routes
        Route::get("/convenios", [ConvenioController::class, "index"])->name('convenios.index');
        Route::post("/convenios", [ConvenioController::class, "store"])->name('convenios.store');
        Route::put("/convenios/{id}", [ConvenioController::class, "update"])->name('convenios.update');
        Route::delete("/convenios/{id}", [ConvenioController::class, "destroy"])->name('convenios.destroy');
        Route::get("/convenios/{id}/tuss-procedimentos", [ConvenioController::class, "tussProcedimentos"])->whereNumber('id')->name('convenios.tuss_procedimentos');

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

        // Faturamento
        Route::get("/faturamento/particular", [FaturamentoController::class, "particular"])->name('faturamento.particular');
        Route::get("/faturamento/convenios", [FaturamentoController::class, "convenios"])->name('faturamento.convenios');
        Route::put("/faturamentos/{id}/convenio", [FaturamentoController::class, "updateConvenio"])->whereNumber('id')->name('faturamentos.convenio.update');

        // Financeiro
        Route::get("/contas-receber", [ContasReceberController::class, "index"])->name('financeiro.contas_receber.index');
        Route::post("/faturamentos/{id}/receber-financeiro", [ContasReceberController::class, "receiveConvenio"])->whereNumber('id')->name('financeiro.receber_convenio');

        // Agenda Médica routes
        Route::post("/agenda-medica", [AgendaMedicaController::class, "store"])->name('agenda_medica.store');
        Route::get("/agendamentos/profissionais-por-procedimento", [AgendamentoController::class, "profissionaisPorProcedimento"])->name('agendamentos.profissionais_por_procedimento');
        Route::get("/agendamentos", [AgendamentoController::class, "index"])->name('agendamentos.index');
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
        Route::post("/profissionais-saude", [\App\Http\Controllers\ProfissionalSaudeController::class, "store"])->name('profissionais_saude.store');
        Route::put("/profissionais-saude/{id}", [\App\Http\Controllers\ProfissionalSaudeController::class, "update"])->name('profissionais_saude.update');
        Route::delete("/profissionais-saude/{id}", [\App\Http\Controllers\ProfissionalSaudeController::class, "destroy"])->name('profissionais_saude.destroy');

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
        Route::put("/parametros/tipo-sanguineo/{id}", [VelzonRoutesController::class, "parametros_update_tipo_sanguineo"])->name('parametros.tipo_sanguineo.update');
        Route::delete("/parametros/tipo-sanguineo/{id}", [VelzonRoutesController::class, "parametros_destroy_tipo_sanguineo"])->name('parametros.tipo_sanguineo.destroy');
        Route::post("/parametros/canal-aviso", [VelzonRoutesController::class, "parametros_store_canal_aviso"])->name('parametros.canal_aviso.store');
        Route::put("/parametros/canal-aviso/{id}", [VelzonRoutesController::class, "parametros_update_canal_aviso"])->name('parametros.canal_aviso.update');
        Route::delete("/parametros/canal-aviso/{id}", [VelzonRoutesController::class, "parametros_destroy_canal_aviso"])->name('parametros.canal_aviso.destroy');
        Route::post("/parametros/parentesco", [VelzonRoutesController::class, "parametros_store_parentesco"])->name('parametros.parentesco.store');
        Route::put("/parametros/parentesco/{id}", [VelzonRoutesController::class, "parametros_update_parentesco"])->name('parametros.parentesco.update');
        Route::delete("/parametros/parentesco/{id}", [VelzonRoutesController::class, "parametros_destroy_parentesco"])->name('parametros.parentesco.destroy');

    });
});

// PIX webhook (sem autenticação)
Route::post('/pix/webhook', [\App\Http\Controllers\PagamentoController::class, 'pixWebhook'])->name('pix.webhook');
Route::post('/pix/mp/webhook', [\App\Http\Controllers\PagamentoController::class, 'mpWebhook'])->name('pix.mp.webhook');
