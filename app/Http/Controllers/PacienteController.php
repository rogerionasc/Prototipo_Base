<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Endereco;
use App\Models\EstadoCivil;
use App\Models\TipoSanguineo;
use App\Models\CanalAviso;
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
            'id',
            'nome',
            'cpf',
            'email',
            'celular',
            DB::raw("DATE_FORMAT(data_nascimento, '%d-%m-%Y') AS data_nascimento"),
            'convenio'
        )->get();
        $estadosCivis = EstadoCivil::select('id', 'descricao')->orderBy('descricao')->get();
        $tiposSanguineos = TipoSanguineo::select('id', 'descricao')->orderBy('descricao')->get();
        $canaisAviso = CanalAviso::select('id', 'nome')->orderBy('nome')->get();

        return Inertia::render("Pacientes/Index", [
            'pacientes' => $pacientes,
            'estadosCivis' => $estadosCivis,
            'tiposSanguineos' => $tiposSanguineos,
            'canaisAviso' => $canaisAviso,
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
            'convenio' => ['nullable', 'string', 'max:255'],
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
        ], [
            'cpf.unique' => 'O CPF informado já está cadastrado.',
            'cpf.required' => 'O campo CPF é obrigatório.',
            'cpf.max' => 'O campo CPF deve ter no máximo 14 caracteres.',
            'cpf.min' => 'O campo CPF deve ter no mínimo 14 caracteres.',
            'estado_civil_id.exists' => 'Selecione um estado civil válido.',
            'canal_aviso_id.exists' => 'Selecione um canal de aviso válido.',
            'tipo_sanguineo_id.exists' => 'Selecione um tipo sanguíneo válido.',
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

        Paciente::create($data);

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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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
