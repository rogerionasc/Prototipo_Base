<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Endereco;
use App\Models\EstadoCivil;
use App\Models\TipoSanguineo;
use App\Models\CanalAviso;
use App\Models\Convenio;
use App\Models\Parentesco;
use App\Models\Responsavel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PacienteController extends Controller
{
    private function getConveniosAtivosDoPaciente(int $pacienteId)
    {
        $convenios = DB::table('paciente_convenio as pc')
            ->join('convenios as c', 'c.id', '=', 'pc.convenio_id')
            ->select(
                'c.id',
                'c.descricao',
                'c.tipo',
                'pc.numero_carteira',
                'pc.plano',
                DB::raw("DATE_FORMAT(pc.validade, '%d-%m-%Y') AS validade")
            )
            ->where('pc.paciente_id', $pacienteId)
            ->where('pc.ativo', 1)
            ->whereNull('pc.deleted_at')
            ->whereNull('c.deleted_at')
            ->orderBy('c.descricao')
            ->get();

        $particular = DB::table('convenios')
            ->select('id', 'descricao', 'tipo', DB::raw("'' AS numero_carteira"), DB::raw("'' AS plano"), DB::raw("NULL AS validade"))
            ->whereNull('deleted_at')
            ->where(function($q) {
                $q->whereRaw('UPPER(tipo) = ?', ['PARTICULAR'])
                  ->orWhereRaw('UPPER(descricao) = ?', ['PARTICULAR']);
            })
            ->first();

        if ($particular && !$convenios->contains('id', $particular->id)) {
            $convenios->push($particular);
        }

        return $convenios;
    }

    private function pacientesSearchRows(string $q)
    {
        $query = DB::table('pacientes as p')
            ->select('p.id', 'p.nome', 'p.cpf')
            ->whereNull('p.deleted_at');

        $qDigits = preg_replace('/\D/', '', $q);
        $qId = ctype_digit($q) ? (int)$q : 0;

        $query->where(function ($w) use ($q, $qDigits, $qId) {
            if ($qId > 0) {
                $w->orWhere('p.id', $qId);
            }
            $w->orWhere('p.nome', 'like', '%' . $q . '%');
            if ($qDigits !== '') {
                $w->orWhereRaw("REPLACE(REPLACE(p.cpf, '.', ''), '-', '') LIKE ?", ['%' . $qDigits . '%']);
            } else {
                $w->orWhere('p.cpf', 'like', '%' . $q . '%');
            }
        });

        return $query
            ->orderBy('p.nome')
            ->limit(30)
            ->get();
    }

    public function apiIndex(Request $request)
    {
        $query = Paciente::select(
            'pacientes.id',
            'nome',
            'cpf',
            'rg',
            'email',
            'celular',
            'sexo',
            'tem_responsavel',
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
              ->whereNull('pc.deleted_at')
              ->whereNull('cv.deleted_at')
              ->select(DB::raw("GROUP_CONCAT(DISTINCT cv.descricao ORDER BY cv.descricao SEPARATOR ', ')"));
        }, 'convenio')
        ->selectSub(function ($q) {
            $q->from('paciente_convenio as pc')
              ->whereColumn('pc.paciente_id', 'pacientes.id')
              ->where('pc.ativo', 1)
              ->whereNull('pc.deleted_at')
              ->select(DB::raw("GROUP_CONCAT(DISTINCT pc.convenio_id ORDER BY pc.convenio_id SEPARATOR ',')"));
        }, 'convenio_ids')
        ->selectSub(function ($q) {
            $q->from('paciente_responsavel as pr')
              ->join('responsaveis as r', 'r.id', '=', 'pr.responsavel_id')
              ->whereColumn('pr.paciente_id', 'pacientes.id')
              ->whereNull('r.deleted_at')
              ->orderByDesc('pr.created_at')
              ->limit(1)
              ->select('r.nome');
        }, 'responsavel_nome')
        ->selectSub(function ($q) {
            $q->from('paciente_responsavel as pr')
              ->join('responsaveis as r', 'r.id', '=', 'pr.responsavel_id')
              ->whereColumn('pr.paciente_id', 'pacientes.id')
              ->whereNull('r.deleted_at')
              ->orderByDesc('pr.created_at')
              ->limit(1)
              ->select('r.parentesco_id');
        }, 'responsavel_parentesco_id')
        ->selectSub(function ($q) {
            $q->from('paciente_responsavel as pr')
              ->join('responsaveis as r', 'r.id', '=', 'pr.responsavel_id')
              ->whereColumn('pr.paciente_id', 'pacientes.id')
              ->whereNull('r.deleted_at')
              ->orderByDesc('pr.created_at')
              ->limit(1)
              ->select('r.cpf');
        }, 'responsavel_cpf')
        ->selectSub(function ($q) {
            $q->from('paciente_responsavel as pr')
              ->join('responsaveis as r', 'r.id', '=', 'pr.responsavel_id')
              ->whereColumn('pr.paciente_id', 'pacientes.id')
              ->whereNull('r.deleted_at')
              ->orderByDesc('pr.created_at')
              ->limit(1)
              ->select('r.rg');
        }, 'responsavel_rg')
        ->selectSub(function ($q) {
            $q->from('paciente_responsavel as pr')
              ->join('responsaveis as r', 'r.id', '=', 'pr.responsavel_id')
              ->whereColumn('pr.paciente_id', 'pacientes.id')
              ->whereNull('r.deleted_at')
              ->orderByDesc('pr.created_at')
              ->limit(1)
              ->select(DB::raw("DATE_FORMAT(r.data_nascimento, '%Y-%m-%d')"));
        }, 'responsavel_data_nascimento')
        ->selectSub(function ($q) {
            $q->from('paciente_responsavel as pr')
              ->join('responsaveis as r', 'r.id', '=', 'pr.responsavel_id')
              ->whereColumn('pr.paciente_id', 'pacientes.id')
              ->whereNull('r.deleted_at')
              ->orderByDesc('pr.created_at')
              ->limit(1)
              ->select('r.celular');
        }, 'responsavel_celular')
        ->selectSub(function ($q) {
            $q->from('paciente_responsavel as pr')
              ->join('responsaveis as r', 'r.id', '=', 'pr.responsavel_id')
              ->whereColumn('pr.paciente_id', 'pacientes.id')
              ->whereNull('r.deleted_at')
              ->orderByDesc('pr.created_at')
              ->limit(1)
              ->select('r.telefone');
        }, 'responsavel_telefone')
        ->selectSub(function ($q) {
            $q->from('paciente_responsavel as pr')
              ->join('responsaveis as r', 'r.id', '=', 'pr.responsavel_id')
              ->whereColumn('pr.paciente_id', 'pacientes.id')
              ->whereNull('r.deleted_at')
              ->orderByDesc('pr.created_at')
              ->limit(1)
              ->select('r.email');
        }, 'responsavel_email');

        $search = $request->input('q', $request->input('search', ''));
        if (!empty($search)) {
            $qDigits = preg_replace('/\D/', '', $search);
            $qId = ctype_digit($search) ? (int)$search : 0;
            
            $query->where(function ($w) use ($search, $qDigits, $qId) {
                if ($qId > 0) {
                    $w->orWhere('pacientes.id', $qId);
                }
                $w->orWhere('pacientes.nome', 'like', '%' . $search . '%');
                if ($qDigits !== '') {
                    $w->orWhereRaw("REPLACE(REPLACE(pacientes.cpf, '.', ''), '-', '') LIKE ?", ['%' . $qDigits . '%']);
                } else {
                    $w->orWhere('pacientes.cpf', 'like', '%' . $search . '%');
                }
            });
        }

        return $query->paginate($request->input('limit', 10));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $estadosCivis = EstadoCivil::select('id', 'descricao')->orderBy('descricao')->get();
        $tiposSanguineos = TipoSanguineo::select('id', 'descricao')->orderBy('descricao')->get();
        $canaisAviso = CanalAviso::select('id', 'nome')->orderBy('nome')->get();
        $convenios = Convenio::select('id','descricao','tipo')->orderBy('descricao')->get();
        $parentescos = Parentesco::select('id', 'descricao')->orderBy('descricao')->get();
        $procedimentos = \App\Models\Procedimento::select('id','nome','valor','eh_tratamento','quantidade_sessoes')->where('ativo', 1)->orderBy('nome')->get();

        return Inertia::render("Pacientes/Index", [
            'pacientes' => [],
            'estadosCivis' => $estadosCivis,
            'tiposSanguineos' => $tiposSanguineos,
            'canaisAviso' => $canaisAviso,
            'convenios' => $convenios,
            'parentescos' => $parentescos,
            'procedimentos' => $procedimentos,
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
            'tem_responsavel' => ['nullable', 'boolean'],
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
            'convenios' => ['nullable', 'array'],
            'convenios.*.convenio_id' => ['required', 'integer', 'exists:convenios,id'],
            'convenios.*.numero_carteira' => ['nullable', 'string', 'max:30'],
        ], [
            'cpf.unique' => 'O CPF informado já está cadastrado.',
            'cpf.required' => 'O campo CPF é obrigatório.',
            'cpf.max' => 'O campo CPF deve ter no máximo 14 caracteres.',
            'cpf.min' => 'O campo CPF deve ter no mínimo 14 caracteres.',
            'estado_civil_id.exists' => 'Selecione um estado civil válido.',
            'canal_aviso_id.exists' => 'Selecione um canal de aviso válido.',
            'tipo_sanguineo_id.exists' => 'Selecione um tipo sanguíneo válido.',
            'convenios.*.convenio_id.exists' => 'Selecione convênios válidos.',
        ]);

        // dd($data);
        $responsavelData = $request->validate([
            'responsavel_nome' => ['required_if:tem_responsavel,1,true,on', 'nullable', 'string', 'max:255'],
            'responsavel_parentesco_id' => ['required_if:tem_responsavel,1,true,on', 'nullable', 'integer', 'exists:parentescos,id'],
            'responsavel_cpf' => ['nullable', 'string', 'max:14'],
            'responsavel_rg' => ['nullable', 'string', 'max:20'],
            'responsavel_data_nascimento' => ['nullable', 'date_format:Y-m-d'],
            'responsavel_celular' => ['nullable', 'string', 'max:30'],
            'responsavel_telefone' => ['nullable', 'string', 'max:30'],
            'responsavel_email' => ['nullable', 'email', 'max:255'],
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
            $endereco = Endereco::create($enderecoData);
            $data['endereco_id'] = $endereco->id;
        } else {
            $data['endereco_id'] = null;
        }

        if (!$this->isValidCpf($data['cpf'])) {
            return back()->withErrors(['cpf' => 'CPF inválido.'])->withInput();
        }

        $paciente = Paciente::create($data);

        if ($request->boolean('tem_responsavel')) {
            $r = Responsavel::create([
                'nome' => $responsavelData['responsavel_nome'] ?? null,
                'parentesco_id' => $responsavelData['responsavel_parentesco_id'] ?? null,
                'cpf' => $responsavelData['responsavel_cpf'] ?? null,
                'rg' => $responsavelData['responsavel_rg'] ?? null,
                'data_nascimento' => $responsavelData['responsavel_data_nascimento'] ?? null,
                'celular' => $responsavelData['responsavel_celular'] ?? null,
                'telefone' => $responsavelData['responsavel_telefone'] ?? null,
                'email' => $responsavelData['responsavel_email'] ?? null,
            ]);
            if ($r && $r->id) {
                $paciente->responsaveis()->syncWithoutDetaching([$r->id]);
            }
        }

        // Save convenios
        if (isset($data['convenios']) && is_array($data['convenios'])) {
            $now = now();
            DB::table('paciente_convenio')
                ->where('paciente_id', $paciente->id)
                ->update(['ativo' => false, 'updated_at' => $now]);
            
            foreach ($data['convenios'] as $conv) {
                $updated = DB::table('paciente_convenio')
                    ->where('paciente_id', $paciente->id)
                    ->where('convenio_id', $conv['convenio_id'])
                    ->update([
                        'ativo' => true,
                        'numero_carteira' => $conv['numero_carteira'] ?? null,
                        'deleted_at' => null,
                        'updated_at' => $now
                    ]);
                
                if (!$updated) {
                    DB::table('paciente_convenio')->insert([
                        'paciente_id' => $paciente->id,
                        'convenio_id' => $conv['convenio_id'],
                        'numero_carteira' => $conv['numero_carteira'] ?? null,
                        'plano' => null,
                        'validade' => null,
                        'ativo' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]);
                }
            }
        }

        return back()->with('success', 'Paciente salvo com sucesso');
    }

    public function show(string $id)
    {
        $paciente = Paciente::select(
            'pacientes.*',
            DB::raw("DATE_FORMAT(pacientes.data_nascimento, '%Y-%m-%d') AS data_nascimento"),
            DB::raw("COALESCE(e.cep,'') AS cep"),
            DB::raw("COALESCE(e.endereco,'') AS endereco"),
            DB::raw("COALESCE(e.numero,'') AS numero"),
            DB::raw("COALESCE(e.bairro,'') AS bairro"),
            DB::raw("COALESCE(e.cidade,'') AS cidade"),
            DB::raw("COALESCE(e.complemento,'') AS complemento"),
        )
        ->leftJoin('enderecos as e', 'e.id', '=', 'pacientes.endereco_id')
        ->selectSub(function ($q) {
            $q->from('paciente_responsavel as pr')
              ->join('responsaveis as r', 'r.id', '=', 'pr.responsavel_id')
              ->whereColumn('pr.paciente_id', 'pacientes.id')
              ->whereNull('r.deleted_at')
              ->orderByDesc('pr.created_at')
              ->limit(1)
              ->select('r.nome');
        }, 'responsavel_nome')
        ->selectSub(function ($q) {
            $q->from('paciente_responsavel as pr')
              ->join('responsaveis as r', 'r.id', '=', 'pr.responsavel_id')
              ->whereColumn('pr.paciente_id', 'pacientes.id')
              ->whereNull('r.deleted_at')
              ->orderByDesc('pr.created_at')
              ->limit(1)
              ->select('r.parentesco_id');
        }, 'responsavel_parentesco_id')
        ->selectSub(function ($q) {
            $q->from('paciente_responsavel as pr')
              ->join('responsaveis as r', 'r.id', '=', 'pr.responsavel_id')
              ->whereColumn('pr.paciente_id', 'pacientes.id')
              ->whereNull('r.deleted_at')
              ->orderByDesc('pr.created_at')
              ->limit(1)
              ->select('r.cpf');
        }, 'responsavel_cpf')
        ->selectSub(function ($q) {
            $q->from('paciente_responsavel as pr')
              ->join('responsaveis as r', 'r.id', '=', 'pr.responsavel_id')
              ->whereColumn('pr.paciente_id', 'pacientes.id')
              ->whereNull('r.deleted_at')
              ->orderByDesc('pr.created_at')
              ->limit(1)
              ->select('r.rg');
        }, 'responsavel_rg')
        ->selectSub(function ($q) {
            $q->from('paciente_responsavel as pr')
              ->join('responsaveis as r', 'r.id', '=', 'pr.responsavel_id')
              ->whereColumn('pr.paciente_id', 'pacientes.id')
              ->whereNull('r.deleted_at')
              ->orderByDesc('pr.created_at')
              ->limit(1)
              ->select(DB::raw("DATE_FORMAT(r.data_nascimento, '%Y-%m-%d')"));
        }, 'responsavel_data_nascimento')
        ->selectSub(function ($q) {
            $q->from('paciente_responsavel as pr')
              ->join('responsaveis as r', 'r.id', '=', 'pr.responsavel_id')
              ->whereColumn('pr.paciente_id', 'pacientes.id')
              ->whereNull('r.deleted_at')
              ->orderByDesc('pr.created_at')
              ->limit(1)
              ->select('r.celular');
        }, 'responsavel_celular')
        ->selectSub(function ($q) {
            $q->from('paciente_responsavel as pr')
              ->join('responsaveis as r', 'r.id', '=', 'pr.responsavel_id')
              ->whereColumn('pr.paciente_id', 'pacientes.id')
              ->whereNull('r.deleted_at')
              ->orderByDesc('pr.created_at')
              ->limit(1)
              ->select('r.telefone');
        }, 'responsavel_telefone')
        ->selectSub(function ($q) {
            $q->from('paciente_responsavel as pr')
              ->join('responsaveis as r', 'r.id', '=', 'pr.responsavel_id')
              ->whereColumn('pr.paciente_id', 'pacientes.id')
              ->whereNull('r.deleted_at')
              ->orderByDesc('pr.created_at')
              ->limit(1)
              ->select('r.email');
        }, 'responsavel_email')
        ->findOrFail($id);

        return response()->json($paciente);
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
            'tem_responsavel' => ['nullable', 'boolean'],
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
            'convenios' => ['nullable', 'array'],
            'convenios.*.convenio_id' => ['required', 'integer', 'exists:convenios,id'],
            'convenios.*.numero_carteira' => ['nullable', 'string', 'max:30'],
        ], [
            'cpf.unique' => 'O CPF informado já está cadastrado.',
            'cpf.required' => 'O campo CPF é obrigatório.',
            'cpf.max' => 'O campo CPF deve ter no máximo 14 caracteres.',
            'cpf.min' => 'O campo CPF deve ter no mínimo 14 caracteres.',
            'estado_civil_id.exists' => 'Selecione um estado civil válido.',
            'canal_aviso_id.exists' => 'Selecione um canal de aviso válido.',
            'tipo_sanguineo_id.exists' => 'Selecione um tipo sanguíneo válido.',
            'convenios.*.convenio_id.exists' => 'Selecione convênios válidos.',
        ]);

        $responsavelData = $request->validate([
            'responsavel_nome' => ['required_if:tem_responsavel,1,true,on', 'nullable', 'string', 'max:255'],
            'responsavel_parentesco_id' => ['required_if:tem_responsavel,1,true,on', 'nullable', 'integer', 'exists:parentescos,id'],
            'responsavel_cpf' => ['nullable', 'string', 'max:14'],
            'responsavel_rg' => ['nullable', 'string', 'max:20'],
            'responsavel_data_nascimento' => ['nullable', 'date_format:Y-m-d'],
            'responsavel_celular' => ['nullable', 'string', 'max:30'],
            'responsavel_telefone' => ['nullable', 'string', 'max:30'],
            'responsavel_email' => ['nullable', 'email', 'max:255'],
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

        if ($request->boolean('tem_responsavel')) {
            $existing = $paciente->responsaveis()->withTrashed()->orderBy('paciente_responsavel.created_at', 'desc')->first();
            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->update([
                    'nome' => $responsavelData['responsavel_nome'] ?? $existing->nome,
                    'parentesco_id' => $responsavelData['responsavel_parentesco_id'] ?? $existing->parentesco_id,
                    'cpf' => $responsavelData['responsavel_cpf'] ?? $existing->cpf,
                    'rg' => $responsavelData['responsavel_rg'] ?? $existing->rg,
                    'data_nascimento' => $responsavelData['responsavel_data_nascimento'] ?? $existing->data_nascimento,
                    'celular' => $responsavelData['responsavel_celular'] ?? $existing->celular,
                    'telefone' => $responsavelData['responsavel_telefone'] ?? $existing->telefone,
                    'email' => $responsavelData['responsavel_email'] ?? $existing->email,
                ]);
            } else {
                $r = Responsavel::create([
                    'nome' => $responsavelData['responsavel_nome'] ?? null,
                    'parentesco_id' => $responsavelData['responsavel_parentesco_id'] ?? null,
                    'cpf' => $responsavelData['responsavel_cpf'] ?? null,
                    'rg' => $responsavelData['responsavel_rg'] ?? null,
                    'data_nascimento' => $responsavelData['responsavel_data_nascimento'] ?? null,
                    'celular' => $responsavelData['responsavel_celular'] ?? null,
                    'telefone' => $responsavelData['responsavel_telefone'] ?? null,
                    'email' => $responsavelData['responsavel_email'] ?? null,
                ]);
                if ($r && $r->id) {
                    $paciente->responsaveis()->syncWithoutDetaching([$r->id]);
                }
            }
        } else {
            $existing = $paciente->responsaveis()->withTrashed()->orderBy('paciente_responsavel.created_at', 'desc')->first();
            if ($existing && !$existing->trashed()) {
                $existing->delete();
            }
            // Mantém o vínculo na tabela pivô para futura restauração.
        }

        // Save convenios
        if (isset($data['convenios']) && is_array($data['convenios'])) {
            $now = now();
            DB::table('paciente_convenio')
                ->where('paciente_id', $paciente->id)
                ->update(['ativo' => false, 'updated_at' => $now]);
            
            foreach ($data['convenios'] as $conv) {
                $updated = DB::table('paciente_convenio')
                    ->where('paciente_id', $paciente->id)
                    ->where('convenio_id', $conv['convenio_id'])
                    ->update([
                        'ativo' => true,
                        'numero_carteira' => $conv['numero_carteira'] ?? null,
                        'deleted_at' => null,
                        'updated_at' => $now
                    ]);
                
                if (!$updated) {
                    DB::table('paciente_convenio')->insert([
                        'paciente_id' => $paciente->id,
                        'convenio_id' => $conv['convenio_id'],
                        'numero_carteira' => $conv['numero_carteira'] ?? null,
                        'plano' => null,
                        'validade' => null,
                        'ativo' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'deleted_at' => null,
                    ]);
                }
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

    public function convenios(string $id)
    {
        $pid = (int)$id;
        $convenios = $this->getConveniosAtivosDoPaciente($pid);

        return response()->json([
            'convenios' => $convenios,
        ]);
    }

    public function search(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        if ($q === '') {
            return response()->json(['pacientes' => []]);
        }
        $rows = $this->pacientesSearchRows($q);

        return response()->json([
            'pacientes' => $rows,
        ]);
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
