<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Endereco;
use App\Models\EstadoCivil;
use App\Models\TipoSanguineo;
use App\Models\CanalAviso;
use App\Models\Convenio;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PacienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pacientes = Paciente::select(
            'pacientes.id',
            'nome',
            'cpf',
            'rg',
            'email',
            'celular',
            'sexo',
            'receber_avisos',
            'naturalidade',
            'altura',
            'peso',
            'cor_pele',
            'telefone',
            'profissao',
            'escolaridade',
            'nome_mae',
            'nome_pai',
            'observacoes',
            'estado_civil_id',
            'tipo_sanguineo_id',
            'canal_aviso_id',
            DB::raw("DATE_FORMAT(pacientes.data_nascimento, '%d-%m-%Y') AS data_nascimento"),
            DB::raw("COALESCE(e.cep,'') AS cep"),
            DB::raw("COALESCE(e.endereco,'') AS endereco"),
            DB::raw("COALESCE(e.numero,'') AS numero"),
            DB::raw("COALESCE(e.bairro,'') AS bairro"),
            DB::raw("COALESCE(e.cidade,'') AS cidade"),
            DB::raw("COALESCE(e.complemento,'') AS complemento"),
        )
        ->leftJoin('enderecos as e', 'e.id', '=', 'pacientes.endereco_id')
        ->selectSub(function ($q) {
            $q->from('paciente_convenio as pc')
              ->join('convenios as cv', 'cv.id', '=', 'pc.convenio_id')
              ->whereColumn('pc.paciente_id', 'pacientes.id')
              ->where('pc.ativo', 1)
              ->orderByDesc('pc.id')
              ->limit(1)
              ->select('cv.descricao');
        }, 'convenio')
        ->selectSub(function ($q) {
            $q->from('paciente_convenio as pc')
              ->whereColumn('pc.paciente_id', 'pacientes.id')
              ->where('pc.ativo', 1)
              ->orderByDesc('pc.id')
              ->limit(1)
              ->select('pc.convenio_id');
        }, 'convenio_id')
        ->get();
        $estadosCivis = EstadoCivil::select('id', 'descricao')->orderBy('descricao')->get();
        $tiposSanguineos = TipoSanguineo::select('id', 'descricao')->orderBy('descricao')->get();
        $canaisAviso = CanalAviso::select('id', 'nome')->orderBy('nome')->get();
        $convenios = Convenio::select('id','descricao')->orderBy('descricao')->get();

        return Inertia::render("Pacientes/Index", [
            'pacientes' => $pacientes,
            'estadosCivis' => $estadosCivis,
            'tiposSanguineos' => $tiposSanguineos,
            'canaisAviso' => $canaisAviso,
            'convenios' => $convenios,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', 'min:14', 'unique:pacientes,cpf'],
            'rg' => ['nullable', 'string', 'max:20'],
            'sexo' => ['nullable', 'string', 'max:20'],
            'data_nascimento' => ['nullable', 'date_format:Y-m-d'],
            'naturalidade' => ['nullable', 'string', 'max:255'],
            'estado_civil_id' => ['nullable', 'integer', 'exists:estado_civil,id'],
            'altura' => ['nullable', 'numeric'],
            'peso' => ['nullable', 'numeric'],
            'cor_pele' => ['nullable', 'string', 'max:255'],
            'receber_avisos' => ['nullable', 'boolean'],
            'celular' => ['nullable', 'string', 'max:30'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'canal_aviso_id' => ['nullable', 'integer', 'exists:canais_aviso,id'],
            'profissao' => ['nullable', 'string', 'max:255'],
            'escolaridade' => ['nullable', 'string', 'max:255'],
            'nome_mae' => ['nullable', 'string', 'max:255'],
            'nome_pai' => ['nullable', 'string', 'max:255'],
            'tipo_sanguineo_id' => ['nullable', 'integer', 'exists:tipo_sanguineo,id'],
            'observacoes' => ['nullable', 'string'],
            'convenio_id' => ['nullable', 'integer', 'exists:convenios,id'],
        ], [
            'cpf.unique' => 'O CPF informado já está cadastrado.',
            'cpf.required' => 'O campo CPF é obrigatório.',
            'cpf.max' => 'O campo CPF deve ter no máximo 14 caracteres.',
            'cpf.min' => 'O campo CPF deve ter no mínimo 14 caracteres.',
            'estado_civil_id.exists' => 'Selecione um estado civil válido.',
            'canal_aviso_id.exists' => 'Selecione um canal de aviso válido.',
            'tipo_sanguineo_id.exists' => 'Selecione um tipo sanguíneo válido.',
            'convenio_id.exists' => 'Selecione um convênio válido.',
        ]);

        // dd($data);

        foreach (['estado_civil_id', 'canal_aviso_id', 'tipo_sanguineo_id'] as $fk) {
            if (empty($data[$fk])) {
                $data[$fk] = null;
            }
        }

        $enderecoData = $request->validate([
            'cep' => ['nullable', 'string', 'max:9'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'complemento' => ['nullable', 'string', 'max:255'],
        ]);
        $hasEndereco = false;
        foreach (['cep', 'endereco', 'numero', 'bairro', 'cidade', 'complemento'] as $campo) {
            if (!empty($enderecoData[$campo])) {
                $hasEndereco = true;
                break;
            }
        }
        if ($hasEndereco) {
            $endereco = Endereco::create($enderecoData);
            $data['endereco_id'] = $endereco->id;
        } else {
            $data['endereco_id'] = null;
        }

        if (!$this->isValidCpf($data['cpf'])) {
            return back()->withErrors(['cpf' => 'CPF inválido.'])->withInput();
        }

        $paciente = Paciente::create($data);

        if (!empty($data['convenio_id'])) {
            DB::table('paciente_convenio')
                ->where('paciente_id', $paciente->id)
                ->update(['ativo' => false]);
            DB::table('paciente_convenio')
                ->updateOrInsert(
                    ['paciente_id' => $paciente->id, 'convenio_id' => $data['convenio_id']],
                    ['ativo' => true]
                );
        }

        return back()->with('success', 'Paciente salvo com sucesso');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $paciente = Paciente::findOrFail($id);

        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', 'min:14', 'unique:pacientes,cpf,' . $paciente->id],
            'rg' => ['nullable', 'string', 'max:20'],
            'sexo' => ['nullable', 'string', 'max:20'],
            'data_nascimento' => ['nullable', 'date_format:Y-m-d'],
            'naturalidade' => ['nullable', 'string', 'max:255'],
            'estado_civil_id' => ['nullable', 'integer', 'exists:estado_civil,id'],
            'altura' => ['nullable', 'numeric'],
            'peso' => ['nullable', 'numeric'],
            'cor_pele' => ['nullable', 'string', 'max:255'],
            'receber_avisos' => ['nullable', 'boolean'],
            'celular' => ['nullable', 'string', 'max:30'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'canal_aviso_id' => ['nullable', 'integer', 'exists:canais_aviso,id'],
            'profissao' => ['nullable', 'string', 'max:255'],
            'escolaridade' => ['nullable', 'string', 'max:255'],
            'nome_mae' => ['nullable', 'string', 'max:255'],
            'nome_pai' => ['nullable', 'string', 'max:255'],
            'tipo_sanguineo_id' => ['nullable', 'integer', 'exists:tipo_sanguineo,id'],
            'observacoes' => ['nullable', 'string'],
            'convenio_id' => ['nullable', 'integer', 'exists:convenios,id'],
        ], [
            'cpf.unique' => 'O CPF informado já está cadastrado.',
            'cpf.required' => 'O campo CPF é obrigatório.',
            'cpf.max' => 'O campo CPF deve ter no máximo 14 caracteres.',
            'cpf.min' => 'O campo CPF deve ter no mínimo 14 caracteres.',
            'estado_civil_id.exists' => 'Selecione um estado civil válido.',
            'canal_aviso_id.exists' => 'Selecione um canal de aviso válido.',
            'tipo_sanguineo_id.exists' => 'Selecione um tipo sanguíneo válido.',
            'convenio_id.exists' => 'Selecione um convênio válido.',
        ]);

        foreach (['estado_civil_id', 'canal_aviso_id', 'tipo_sanguineo_id'] as $fk) {
            if (empty($data[$fk])) {
                $data[$fk] = null;
            }
        }

        $enderecoData = $request->validate([
            'cep' => ['nullable', 'string', 'max:9'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'complemento' => ['nullable', 'string', 'max:255'],
        ]);

        $hasEndereco = false;
        foreach (['cep', 'endereco', 'numero', 'bairro', 'cidade', 'complemento'] as $campo) {
            if (!empty($enderecoData[$campo])) {
                $hasEndereco = true;
                break;
            }
        }

        if ($hasEndereco) {
            if ($paciente->endereco_id) {
                $endereco = Endereco::find($paciente->endereco_id);
                if ($endereco) {
                    $endereco->update($enderecoData);
                } else {
                    $endereco = Endereco::create($enderecoData);
                    $data['endereco_id'] = $endereco->id;
                }
            } else {
                $endereco = Endereco::create($enderecoData);
                $data['endereco_id'] = $endereco->id;
            }
        } else {
            $data['endereco_id'] = null;
        }

        if (!$this->isValidCpf($data['cpf'])) {
            return back()->withErrors(['cpf' => 'CPF inválido.'])->withInput();
        }

        $paciente->update($data);

        if (array_key_exists('convenio_id', $data)) {
            if (!empty($data['convenio_id'])) {
                DB::table('paciente_convenio')
                    ->where('paciente_id', $paciente->id)
                    ->update(['ativo' => false]);
                DB::table('paciente_convenio')
                    ->updateOrInsert(
                        ['paciente_id' => $paciente->id, 'convenio_id' => $data['convenio_id']],
                        ['ativo' => true]
                    );
            } else {
                DB::table('paciente_convenio')
                    ->where('paciente_id', $paciente->id)
                    ->update(['ativo' => false]);
            }
        }

        return back()->with('success', 'Paciente atualizado com sucesso');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $paciente = Paciente::findOrFail($id);
        $paciente->delete();
        return back()->with('success', 'Paciente excluído com sucesso');
    }

    public function destroyMany(Request $request)
    {
        $payload = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:pacientes,id'],
        ]);
        $ids = $payload['ids'] ?? [];
        if (empty($ids)) {
            return back()->with('info', 'Nenhum paciente selecionado');
        }
        Paciente::whereIn('id', $ids)->delete();
        return back()->with('success', 'Pacientes excluídos com sucesso');
    }

    private function isValidCpf(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf ?? '');
        if (!$cpf || strlen($cpf) !== 11) {
            return false;
        }
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        $sum1 = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum1 += intval($cpf[$i]) * (10 - $i);
        }
        $rest1 = $sum1 % 11;
        $digit1 = ($rest1 < 2) ? 0 : 11 - $rest1;
        if (intval($cpf[9]) !== $digit1) {
            return false;
        }
        $sum2 = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum2 += intval($cpf[$i]) * (11 - $i);
        }
        $rest2 = $sum2 % 11;
        $digit2 = ($rest2 < 2) ? 0 : 11 - $rest2;
        if (intval($cpf[10]) !== $digit2) {
            return false;
        }
        return true;
    }
}
