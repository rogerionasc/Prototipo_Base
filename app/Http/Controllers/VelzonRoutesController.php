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
use App\Models\Comorbidade;

class VelzonRoutesController extends Controller
{


    public function componentes()
    {
        return Inertia::render('Componentes/Index');
    }

    public function configuracao()
    {
        $estados = EstadoCivil::select('id', 'descricao')->orderBy('descricao')->get();
        $tipos = TipoSanguineo::select('id', 'descricao')->orderBy('descricao')->get();
        $canais = CanalAviso::select('id', 'nome')->orderBy('nome')->get();
        $parentescos = Parentesco::select('id', 'descricao')->orderBy('descricao')->get();
        $especialidades = Especialidade::with(['procedimentos:id,nome'])->select('id', 'nome', 'codigo', 'descricao', 'ativo')->orderBy('nome')->get();
        $categoriasProcedimento = CategoriaProcedimento::select('id', 'nome')->orderBy('nome')->get();
        $procedimentos = Procedimento::select('id', 'nome', 'descricao', 'categoria_id', 'eh_tratamento', 'quantidade_sessoes', 'valor', 'comissao_percentual', 'ativo')->orderBy('nome')->get();
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

    public function configuracaoParametrizacao()
    {
        $estados = EstadoCivil::select('id', 'descricao')->orderBy('descricao')->get();
        $tipos = TipoSanguineo::select('id', 'descricao')->orderBy('descricao')->get();
        $canais = CanalAviso::select('id', 'nome')->orderBy('nome')->get();
        $parentescos = Parentesco::select('id', 'descricao')->orderBy('descricao')->get();
        $categoriasProcedimento = CategoriaProcedimento::select('id', 'nome')->orderBy('nome')->get();
        $comorbidades = Comorbidade::select('id', 'nome')->orderBy('nome')->get();

        return Inertia::render('Parametrizacao/Sistema/Index', [
            'estadosCivis' => $estados,
            'tiposSanguineos' => $tipos,
            'canaisAviso' => $canais,
            'parentescos' => $parentescos,
            'categoriasProcedimento' => $categoriasProcedimento,
            'comorbidades' => $comorbidades,
        ]);
    }

    public function configuracaoTiss()
    {
        $conselhos = \App\Models\Conselho::select('id', 'codigo', 'sigla', 'descricao')->orderBy('sigla')->get();
        $caraterAtendimentos = \App\Models\CaraterAtendimento::select('id', 'codigo', 'descricao')->orderBy('codigo')->get();
        $tabelasReferencia = \App\Models\TabelaReferencia::select('id', 'codigo', 'descricao')->orderBy('codigo')->get();
        $tipoAtendimentos = \App\Models\TipoAtendimento::select('id', 'codigo', 'descricao')->orderBy('codigo')->get();
        $indicacaoIncidencias = \App\Models\IndicacaoIncidencia::select('id', 'codigo', 'descricao')->orderBy('codigo')->get();
        $tipoConsultas = \App\Models\TipoConsulta::select('id', 'codigo', 'descricao')->orderBy('codigo')->get();
        $motivosEncerramento = \App\Models\MotivoEncerramento::select('id', 'codigo', 'descricao')->orderBy('codigo')->get();
        $viasAcesso = \App\Models\ViaAcesso::select('id', 'codigo', 'descricao')->orderBy('codigo')->get();
        $tecnicasUtilizadas = \App\Models\TecnicaUtilizada::select('id', 'codigo', 'descricao')->orderBy('codigo')->get();
        $grausParticipacao = \App\Models\GrauParticipacao::select('id', 'codigo', 'descricao')->orderBy('codigo')->get();

        return Inertia::render('Parametrizacao/Tiss/Index', [
            'conselhos' => $conselhos,
            'caraterAtendimentos' => $caraterAtendimentos,
            'tabelasReferencia' => $tabelasReferencia,
            'tipoAtendimentos' => $tipoAtendimentos,
            'indicacaoIncidencias' => $indicacaoIncidencias,
            'tipoConsultas' => $tipoConsultas,
            'motivosEncerramento' => $motivosEncerramento,
            'viasAcesso' => $viasAcesso,
            'tecnicasUtilizadas' => $tecnicasUtilizadas,
            'grausParticipacao' => $grausParticipacao,
        ]);
    }

    public function configuracaoEspecialidades()
    {
        $especialidades = Especialidade::with(['procedimentos:id,nome'])->select('id', 'nome', 'codigo', 'descricao', 'ativo')->orderBy('nome')->get();
        $procedimentos = Procedimento::select('id', 'nome', 'descricao', 'categoria_id', 'eh_tratamento', 'quantidade_sessoes', 'valor', 'comissao_percentual', 'ativo')->orderBy('nome')->get();
        $tuss = \App\Models\Tuss::select('id', 'tabela', 'codigo', 'descricao')->orderBy('descricao')->get();
        return Inertia::render('Especialidades/Index', [
            'especialidades' => $especialidades,
            'procedimentos' => $procedimentos,
            'tuss' => $tuss,
        ]);
    }

    public function configuracaoTuss()
    {
        return Inertia::render('Tuss/Index');
    }

    public function configuracaoCid()
    {
        return Inertia::render('Cids/Index');
    }

    public function configuracaoProcedimentos()
    {
        $categoriasProcedimento = CategoriaProcedimento::select('id', 'nome')->orderBy('nome')->get();
        $procedimentos = Procedimento::select('id', 'nome', 'descricao', 'categoria_id', 'eh_tratamento', 'quantidade_sessoes', 'valor', 'comissao_percentual', 'ativo')
            ->orderBy('nome')
            ->get();
        return Inertia::render('Procedimentos/Index', [
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
        $profissionais = \App\Models\Pessoa::where('pessoas.id', '!=', 1)->select(
            'pessoas.id',
            'nome',
            'cpf',
            'rg',
            'sexo',
            DB::raw("DATE_FORMAT(pessoas.data_nascimento, '%Y-%m-%d') AS data_nascimento"),
            'naturalidade',
            'estado_civil_id',
            'cnes',
            'conselho_id',
            'numero_conselho',
            'uf_conselho',
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
            ->leftJoin('enderecos as e', 'e.id', '=', 'pessoas.endereco_id')
            ->whereNotNull('conselho_id')
            ->with(['conselho', 'agendas', 'especialidades' => function ($q) {
                $q->select('especialidades.id', 'nome')->withPivot('qre');
            }])
            ->orderBy('nome')
            ->get();
        $especialidades = \App\Models\Especialidade::select('id', 'nome', 'codigo', 'descricao', 'ativo')->orderBy('nome')->get();
        $estadosCivis = \App\Models\EstadoCivil::select('id', 'descricao')->orderBy('descricao')->get();
        $conselhos = \App\Models\Conselho::select('id', 'codigo', 'sigla', 'descricao')->orderBy('sigla')->get();
        return Inertia::render('Medicos/Index', [
            'profissionais' => $profissionais,
            'especialidades' => $especialidades,
            'estadosCivis' => $estadosCivis,
            'conselhos' => $conselhos,
        ]);
    }

    public function empregados()
    {
        $pessoas = \App\Models\Pessoa::where('pessoas.id', '!=', 1)->select(
            'pessoas.id',
            'nome',
            'cpf',
            'rg',
            'sexo',
            DB::raw("DATE_FORMAT(pessoas.data_nascimento, '%Y-%m-%d') AS data_nascimento"),
            'naturalidade',
            'estado_civil_id',
            'cnes',
            'conselho_id',
            'numero_conselho',
            'uf_conselho',
            'cargo',
            'endereco_id',
            'celular',
            'telefone',
            'email',
            'observacoes',
            DB::raw("COALESCE(e.cep,'') AS cep"),
            DB::raw("COALESCE(e.endereco,'') AS endereco"),
            DB::raw("COALESCE(e.numero,'') AS numero"),
            DB::raw("COALESCE(e.bairro,'') AS bairro"),
            DB::raw("COALESCE(e.cidade,'') AS cidade"),
            DB::raw("COALESCE(e.complemento,'') AS complemento"),
        )
            ->leftJoin('enderecos as e', 'e.id', '=', 'pessoas.endereco_id')
            ->whereNull('conselho_id')
            ->orderBy('nome')
            ->get();
        $estadosCivis = \App\Models\EstadoCivil::select('id', 'descricao')->orderBy('descricao')->get();
        return Inertia::render('Empregados/Index', [
            'profissionais' => $pessoas,
            'estadosCivis' => $estadosCivis,
        ]);
    }

    public function dashboard()
    {
        return Inertia::render('dashboards/Index');
    }


    public function parametros_index()
    {
        $estados = EstadoCivil::select('id', 'descricao')->orderBy('descricao')->get();
        $tipos = TipoSanguineo::select('id', 'descricao')->orderBy('descricao')->get();
        $canais = CanalAviso::select('id', 'nome')->orderBy('nome')->get();
        $parentescos = Parentesco::select('id', 'descricao')->orderBy('descricao')->get();
        return Inertia::render('Parametros/Index', [
            'estadosCivis' => $estados,
            'tiposSanguineos' => $tipos,
            'canaisAviso' => $canais,
            'parentescos' => $parentescos,
        ]);
    }

    public function parametros_store_estado_civil(Request $request)
    {
        $data = $request->validate([
            'descricao' => ['required', 'string', 'max:100', 'unique:estado_civil,descricao'],
        ], [
            'descricao.required' => 'Informe a descrição.',
            'descricao.unique' => 'Este estado civil já está cadastrado.',
        ]);
        EstadoCivil::create($data);
        return back()->with('success', 'Estado civil cadastrado');
    }

    public function parametros_update_estado_civil(Request $request, int $id)
    {
        $data = $request->validate([
            'descricao' => ['required', 'string', 'max:100', 'unique:estado_civil,descricao,' . $id],
        ], [
            'descricao.required' => 'Informe a descrição.',
            'descricao.unique' => 'Este estado civil já está cadastrado.',
        ]);
        $estado = EstadoCivil::findOrFail($id);
        $estado->update($data);
        return back()->with('success', 'Estado civil atualizado');
    }

    public function parametros_destroy_estado_civil(int $id)
    {
        $estado = EstadoCivil::findOrFail($id);
        $estado->delete();
        return back()->with('success', 'Estado civil removido');
    }

    public function parametros_store_tipo_sanguineo(Request $request)
    {
        $data = $request->validate([
            'descricao' => ['required', 'string', 'max:50', 'unique:tipo_sanguineo,descricao'],
        ], [
            'descricao.required' => 'Informe a descrição.',
            'descricao.unique' => 'Este tipo sanguíneo já está cadastrado.',
        ]);
        TipoSanguineo::create($data);
        return back()->with('success', 'Tipo sanguíneo cadastrado');
    }

    public function parametros_update_tipo_sanguineo(Request $request, int $id)
    {
        $data = $request->validate([
            'descricao' => ['required', 'string', 'max:50', 'unique:tipo_sanguineo,descricao,' . $id],
        ], [
            'descricao.required' => 'Informe a descrição.',
            'descricao.unique' => 'Este tipo sanguíneo já está cadastrado.',
        ]);
        $tipo = TipoSanguineo::findOrFail($id);
        $tipo->update($data);
        return back()->with('success', 'Tipo sanguíneo atualizado');
    }

    public function parametros_destroy_tipo_sanguineo(int $id)
    {
        $tipo = TipoSanguineo::findOrFail($id);
        $tipo->delete();
        return back()->with('success', 'Tipo sanguíneo removido');
    }

    public function parametros_store_canal_aviso(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100', 'unique:canais_aviso,nome'],
        ], [
            'nome.required' => 'Informe o nome.',
            'nome.unique' => 'Este canal de aviso já está cadastrado.',
        ]);
        CanalAviso::create($data);
        return back()->with('success', 'Canal de aviso cadastrado');
    }

    public function parametros_update_canal_aviso(Request $request, int $id)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100', 'unique:canais_aviso,nome,' . $id],
        ], [
            'nome.required' => 'Informe o nome.',
            'nome.unique' => 'Este canal de aviso já está cadastrado.',
        ]);
        $canal = CanalAviso::findOrFail($id);
        $canal->update($data);
        return back()->with('success', 'Canal de aviso atualizado');
    }

    public function parametros_destroy_canal_aviso(int $id)
    {
        $canal = CanalAviso::findOrFail($id);
        $canal->delete();
        return back()->with('success', 'Canal de aviso removido');
    }

    public function parametros_store_parentesco(Request $request)
    {
        $data = $request->validate([
            'descricao' => ['required', 'string', 'max:100', 'unique:parentescos,descricao'],
        ], [
            'descricao.required' => 'Informe a descrição.',
            'descricao.unique' => 'Este parentesco já está cadastrado.',
        ]);
        Parentesco::create($data);
        return back()->with('success', 'Parentesco cadastrado');
    }

    public function parametros_update_parentesco(Request $request, int $id)
    {
        $data = $request->validate([
            'descricao' => ['required', 'string', 'max:100', 'unique:parentescos,descricao,' . $id],
        ], [
            'descricao.required' => 'Informe a descrição.',
            'descricao.unique' => 'Este parentesco já está cadastrado.',
        ]);
        $p = Parentesco::findOrFail($id);
        $p->update($data);
        return back()->with('success', 'Parentesco atualizado');
    }

    public function parametros_destroy_parentesco(int $id)
    {
        $p = Parentesco::findOrFail($id);
        $p->delete();
        return back()->with('success', 'Parentesco removido');
    }

    public function parametros_store_categoria_procedimento(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255', 'unique:categorias_procedimento,nome'],
        ], [
            'nome.required' => 'Informe o nome.',
            'nome.unique' => 'Esta categoria já está cadastrada.',
        ]);
        CategoriaProcedimento::create($data);
        return back()->with('success', 'Categoria cadastrada');
    }

    public function parametros_update_categoria_procedimento(Request $request, int $id)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255', 'unique:categorias_procedimento,nome,' . $id],
        ], [
            'nome.required' => 'Informe o nome.',
            'nome.unique' => 'Esta categoria já está cadastrada.',
        ]);
        $cat = CategoriaProcedimento::findOrFail($id);
        $cat->update($data);
        return back()->with('success', 'Categoria atualizada');
    }

    public function parametros_destroy_categoria_procedimento($id)
    {
        $categoria = CategoriaProcedimento::findOrFail($id);
        $categoria->delete();
        return redirect()->back()->with('success', 'Categoria de Procedimento removida com sucesso!');
    }

    public function parametros_store_comorbidade(Request $request)
    {
        $request->validate(['nome' => 'required|string|max:255']);
        Comorbidade::create($request->only('nome'));
        return redirect()->back()->with('success', 'Comorbidade criada com sucesso!');
    }

    public function parametros_update_comorbidade(Request $request, $id)
    {
        $request->validate(['nome' => 'required|string|max:255']);
        $comorbidade = Comorbidade::findOrFail($id);
        $comorbidade->update($request->only('nome'));
        return redirect()->back()->with('success', 'Comorbidade atualizada com sucesso!');
    }

    public function parametros_destroy_comorbidade($id)
    {
        $comorbidade = Comorbidade::findOrFail($id);
        $comorbidade->delete();
        return redirect()->back()->with('success', 'Comorbidade removida com sucesso!');
    }
    public function parametros_store_conselho(Request $request)
    {
        $data = $request->validate([
            'codigo' => 'nullable|string|max:5',
            'sigla' => 'required|string|max:10|unique:conselhos,sigla',
            'descricao' => 'required|string|max:100',
        ]);
        \App\Models\Conselho::create($data);
        return redirect()->back()->with('success', 'Conselho criado com sucesso!');
    }

    public function parametros_update_conselho(Request $request, $id)
    {
        $conselho = \App\Models\Conselho::findOrFail($id);
        $data = $request->validate([
            'codigo' => 'nullable|string|max:5',
            'sigla' => 'required|string|max:10|unique:conselhos,sigla,' . $id,
            'descricao' => 'required|string|max:100',
        ]);
        $conselho->update($data);
        return redirect()->back()->with('success', 'Conselho atualizado com sucesso!');
    }

    public function parametros_destroy_conselho($id)
    {
        $conselho = \App\Models\Conselho::findOrFail($id);
        $conselho->delete();
        return redirect()->back()->with('success', 'Conselho removido com sucesso!');
    }

    public function parametros_store_carater_atendimento(Request $request)
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:2|unique:carater_atendimentos,codigo',
            'descricao' => 'required|string|max:50',
        ]);
        \App\Models\CaraterAtendimento::create($data);
        return redirect()->back()->with('success', 'Caráter de Atendimento criado com sucesso!');
    }

    public function parametros_update_carater_atendimento(Request $request, $id)
    {
        $carater = \App\Models\CaraterAtendimento::findOrFail($id);
        $data = $request->validate([
            'codigo' => 'required|string|max:2|unique:carater_atendimentos,codigo,' . $id,
            'descricao' => 'required|string|max:50',
        ]);
        $carater->update($data);
        return redirect()->back()->with('success', 'Caráter de Atendimento atualizado com sucesso!');
    }

    public function parametros_destroy_carater_atendimento($id)
    {
        $carater = \App\Models\CaraterAtendimento::findOrFail($id);
        $carater->delete();
        return redirect()->back()->with('success', 'Caráter de Atendimento removido com sucesso!');
    }

    public function parametros_store_tabela_referencia(Request $request)
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:2|unique:tabela_referencias,codigo',
            'descricao' => 'required|string|max:100',
        ]);
        \App\Models\TabelaReferencia::create($data);
        return redirect()->back()->with('success', 'Tabela de Referência criada com sucesso!');
    }

    public function parametros_update_tabela_referencia(Request $request, $id)
    {
        $tabela = \App\Models\TabelaReferencia::findOrFail($id);
        $data = $request->validate([
            'codigo' => 'required|string|max:2|unique:tabela_referencias,codigo,' . $id,
            'descricao' => 'required|string|max:100',
        ]);
        $tabela->update($data);
        return redirect()->back()->with('success', 'Tabela de Referência atualizada com sucesso!');
    }

    public function parametros_destroy_tabela_referencia($id)
    {
        $tabela = \App\Models\TabelaReferencia::findOrFail($id);
        $tabela->delete();
        return redirect()->back()->with('success', 'Tabela de Referência removida com sucesso!');
    }
}
