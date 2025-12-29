<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\EstadoCivil;
use App\Models\TipoSanguineo;
use App\Models\CanalAviso;
use App\Models\Parentesco;
use App\Models\Especialidade;
use App\Models\CategoriaProcedimento;
use App\Models\Procedimento;

class VelzonRoutesController extends Controller
{
    // tesrt function
    public function test_func(){
        return Inertia::render('NewPage/index');
    }

    public function componentes() {
        return Inertia::render('Componentes/Index');
    }

    public function configuracao() {
        $estados = EstadoCivil::select('id','descricao')->orderBy('descricao')->get();
        $tipos = TipoSanguineo::select('id','descricao')->orderBy('descricao')->get();
        $canais = CanalAviso::select('id','nome')->orderBy('nome')->get();
        $parentescos = Parentesco::select('id','descricao')->orderBy('descricao')->get();
        $especialidades = Especialidade::select('id','nome','codigo','descricao','ativo')->orderBy('nome')->get();
        $categoriasProcedimento = CategoriaProcedimento::select('id','nome')->orderBy('nome')->get();
        $procedimentos = Procedimento::select('id','nome','descricao','categoria_id','eh_tratamento','quantidade_sessoes','valor','comissao_percentual','ativo')->orderBy('nome')->get();
        return Inertia::render('Configuracao/Index', [
            'estadosCivis' => $estados,
            'tiposSanguineos' => $tipos,
            'canaisAviso' => $canais,
            'parentescos' => $parentescos,
            'especialidades' => $especialidades,
            'categoriasProcedimento' => $categoriasProcedimento,
            'procedimentos' => $procedimentos,
        ]);
    }

    public function usuario()
    {
        return Inertia::render('Users/Index');
    }

    public function medico()
    {
        $profissionais = \App\Models\ProfissionalSaude::select(
                'profissionais_saude.id',
                'nome',
                'cpf',
                'rg',
                'sexo',
                DB::raw("DATE_FORMAT(profissionais_saude.data_nascimento, '%Y-%m-%d') AS data_nascimento"),
                'naturalidade',
                'estado_civil_id',
                'cnes',
                'crm',
                'endereco_id',
                'email',
                'telefone',
                'celular',
                'observacoes',
                DB::raw("COALESCE(e.cep,'') AS cep"),
                DB::raw("COALESCE(e.endereco,'') AS endereco"),
                DB::raw("COALESCE(e.numero,'') AS numero"),
                DB::raw("COALESCE(e.bairro,'') AS bairro"),
                DB::raw("COALESCE(e.cidade,'') AS cidade"),
                DB::raw("COALESCE(e.complemento,'') AS complemento"),
            )
            ->leftJoin('enderecos as e', 'e.id', '=', 'profissionais_saude.endereco_id')
            ->with(['especialidades' => function($q) {
                $q->select('especialidades.id','nome')->withPivot('qre');
            }])
            ->orderBy('nome')
            ->get();
        $especialidades = \App\Models\Especialidade::select('id','nome','codigo','descricao','ativo')->orderBy('nome')->get();
        $estadosCivis = \App\Models\EstadoCivil::select('id','descricao')->orderBy('descricao')->get();
        return Inertia::render('Doctor/Index', [
            'profissionais' => $profissionais,
            'especialidades' => $especialidades,
            'estadosCivis' => $estadosCivis,
        ]);
    }

    public function dashboard()
    {
        return Inertia::render('dashboards/ecommerce/Index');
    }

    public function pages_starter() {
        return Inertia::render('pages/starter');
    }

    public function pages_maintenance() {
        return Inertia::render('pages/maintenance');
    }

    public function pages_coming_soon() {
        return Inertia::render('pages/coming-soon');
    }

    public function auth_signin_basic() {
        return Inertia::render('auth-pages/signin/basic');
    }

    public function auth_signin_cover() {
        return Inertia::render('auth-pages/signin/cover');
    }

    public function auth_signup_basic() {
        return Inertia::render('auth-pages/signup/basic');
    }

    public function auth_signup_cover() {
        return Inertia::render('auth-pages/signup/cover');
    }

    public function auth_reset_pwd_basic() {
        return Inertia::render('auth-pages/reset/basic');
    }

    public function auth_reset_pwd_cover() {
        return Inertia::render('auth-pages/reset/cover');
    }

    public function auth_create_pwd_basic() {
        return Inertia::render('auth-pages/create/basic');
    }

    public function auth_create_pwd_cover() {
        return Inertia::render('auth-pages/create/cover');
    }

    public function auth_lockscreen_basic() {
        return Inertia::render('auth-pages/lockscreen/basic');
    }

    public function auth_lockscreen_cover() {
        return Inertia::render('auth-pages/lockscreen/cover');
    }

    public function auth_twostep_basic() {
        return Inertia::render('auth-pages/twostep/basic');
    }

    public function auth_twostep_cover() {
        return Inertia::render('auth-pages/twostep/cover');
    }

    public function auth_404() {
        return Inertia::render('auth-pages/errors/404');
    }

    public function auth_500() {
        return Inertia::render('auth-pages/errors/500');
    }

    public function auth_404_basic() {
        return Inertia::render('auth-pages/errors/404-basic');
    }

    public function auth_404_cover() {
        return Inertia::render('auth-pages/errors/404-cover');
    }

    public function auth_ofline() {
        return Inertia::render('auth-pages/errors/ofline');
    }

    public function auth_logout_basic() {
        return Inertia::render('auth-pages/logout/basic');
    }

    public function auth_logout_cover() {
        return Inertia::render('auth-pages/logout/cover');
    }

    public function auth_success_msg_basic() {
        return Inertia::render('auth-pages/success-msg/basic');
    }

    public function auth_success_msg_cover() {
        return Inertia::render('auth-pages/success-msg/cover');
    }

    public function parametros_index() {
        $estados = EstadoCivil::select('id','descricao')->orderBy('descricao')->get();
        $tipos = TipoSanguineo::select('id','descricao')->orderBy('descricao')->get();
        $canais = CanalAviso::select('id','nome')->orderBy('nome')->get();
        $parentescos = Parentesco::select('id','descricao')->orderBy('descricao')->get();
        return Inertia::render('Parametros/Index', [
            'estadosCivis' => $estados,
            'tiposSanguineos' => $tipos,
            'canaisAviso' => $canais,
            'parentescos' => $parentescos,
        ]);
    }

    public function parametros_store_estado_civil(Request $request) {
        $data = $request->validate([
            'descricao' => ['required','string','max:100','unique:estado_civil,descricao'],
        ], [
            'descricao.required' => 'Informe a descrição.',
            'descricao.unique' => 'Este estado civil já está cadastrado.',
        ]);
        EstadoCivil::create($data);
        return back()->with('success','Estado civil cadastrado');
    }

    public function parametros_update_estado_civil(Request $request, int $id) {
        $data = $request->validate([
            'descricao' => ['required','string','max:100','unique:estado_civil,descricao,' . $id],
        ], [
            'descricao.required' => 'Informe a descrição.',
            'descricao.unique' => 'Este estado civil já está cadastrado.',
        ]);
        $estado = EstadoCivil::findOrFail($id);
        $estado->update($data);
        return back()->with('success','Estado civil atualizado');
    }

    public function parametros_destroy_estado_civil(int $id) {
        $estado = EstadoCivil::findOrFail($id);
        $estado->delete();
        return back()->with('success','Estado civil removido');
    }

    public function parametros_store_tipo_sanguineo(Request $request) {
        $data = $request->validate([
            'descricao' => ['required','string','max:50','unique:tipo_sanguineo,descricao'],
        ], [
            'descricao.required' => 'Informe a descrição.',
            'descricao.unique' => 'Este tipo sanguíneo já está cadastrado.',
        ]);
        TipoSanguineo::create($data);
        return back()->with('success','Tipo sanguíneo cadastrado');
    }

    public function parametros_update_tipo_sanguineo(Request $request, int $id) {
        $data = $request->validate([
            'descricao' => ['required','string','max:50','unique:tipo_sanguineo,descricao,' . $id],
        ], [
            'descricao.required' => 'Informe a descrição.',
            'descricao.unique' => 'Este tipo sanguíneo já está cadastrado.',
        ]);
        $tipo = TipoSanguineo::findOrFail($id);
        $tipo->update($data);
        return back()->with('success','Tipo sanguíneo atualizado');
    }

    public function parametros_destroy_tipo_sanguineo(int $id) {
        $tipo = TipoSanguineo::findOrFail($id);
        $tipo->delete();
        return back()->with('success','Tipo sanguíneo removido');
    }

    public function parametros_store_canal_aviso(Request $request) {
        $data = $request->validate([
            'nome' => ['required','string','max:100','unique:canais_aviso,nome'],
        ], [
            'nome.required' => 'Informe o nome.',
            'nome.unique' => 'Este canal de aviso já está cadastrado.',
        ]);
        CanalAviso::create($data);
        return back()->with('success','Canal de aviso cadastrado');
    }

    public function parametros_update_canal_aviso(Request $request, int $id) {
        $data = $request->validate([
            'nome' => ['required','string','max:100','unique:canais_aviso,nome,' . $id],
        ], [
            'nome.required' => 'Informe o nome.',
            'nome.unique' => 'Este canal de aviso já está cadastrado.',
        ]);
        $canal = CanalAviso::findOrFail($id);
        $canal->update($data);
        return back()->with('success','Canal de aviso atualizado');
    }

    public function parametros_destroy_canal_aviso(int $id) {
        $canal = CanalAviso::findOrFail($id);
        $canal->delete();
        return back()->with('success','Canal de aviso removido');
    }

    public function parametros_store_parentesco(Request $request) {
        $data = $request->validate([
            'descricao' => ['required','string','max:100','unique:parentescos,descricao'],
        ], [
            'descricao.required' => 'Informe a descrição.',
            'descricao.unique' => 'Este parentesco já está cadastrado.',
        ]);
        Parentesco::create($data);
        return back()->with('success','Parentesco cadastrado');
    }

    public function parametros_update_parentesco(Request $request, int $id) {
        $data = $request->validate([
            'descricao' => ['required','string','max:100','unique:parentescos,descricao,' . $id],
        ], [
            'descricao.required' => 'Informe a descrição.',
            'descricao.unique' => 'Este parentesco já está cadastrado.',
        ]);
        $p = Parentesco::findOrFail($id);
        $p->update($data);
        return back()->with('success','Parentesco atualizado');
    }

    public function parametros_destroy_parentesco(int $id) {
        $p = Parentesco::findOrFail($id);
        $p->delete();
        return back()->with('success','Parentesco removido');
    }

    public function parametros_store_categoria_procedimento(Request $request) {
        $data = $request->validate([
            'nome' => ['required','string','max:255','unique:categorias_procedimento,nome'],
        ], [
            'nome.required' => 'Informe o nome.',
            'nome.unique' => 'Esta categoria já está cadastrada.',
        ]);
        CategoriaProcedimento::create($data);
        return back()->with('success','Categoria cadastrada');
    }

    public function parametros_update_categoria_procedimento(Request $request, int $id) {
        $data = $request->validate([
            'nome' => ['required','string','max:255','unique:categorias_procedimento,nome,' . $id],
        ], [
            'nome.required' => 'Informe o nome.',
            'nome.unique' => 'Esta categoria já está cadastrada.',
        ]);
        $cat = CategoriaProcedimento::findOrFail($id);
        $cat->update($data);
        return back()->with('success','Categoria atualizada');
    }

    public function parametros_destroy_categoria_procedimento(int $id) {
        $cat = CategoriaProcedimento::findOrFail($id);
        $cat->delete();
        return back()->with('success','Categoria removida');
    }
}
