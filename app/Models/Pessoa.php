<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pessoa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pessoas';

    protected $fillable = [
        'nome',
        'cpf',
        'rg',
        'sexo',
        'data_nascimento',
        'naturalidade',
        'estado_civil_id',
        'cnes',
        'crm',
        'cargo',
        'endereco_id',
        'celular',
        'telefone',
        'email',
        'observacoes',
    ];

    public function estadoCivil()
    {
        return $this->belongsTo(EstadoCivil::class, 'estado_civil_id');
    }

    public function endereco()
    {
        return $this->belongsTo(Endereco::class, 'endereco_id');
    }

    public function especialidades()
    {
        return $this->belongsToMany(Especialidade::class, 'profissional_especialidade', 'pessoa_id', 'especialidade_id')
            ->withPivot('qre');
    }

    public function agendas()
    {
        return $this->hasMany(AgendaMedica::class, 'pessoa_id');
    }

    public function atendimentos()
    {
        return $this->hasMany(Atendimento::class, 'pessoa_id');
    }

    public function documentosProntuario()
    {
        return $this->hasMany(DocumentoProntuario::class, 'pessoa_id');
    }

    public function prescricoes()
    {
        return $this->hasMany(Prescricao::class, 'pessoa_id');
    }

    public function solicitacoesExames()
    {
        return $this->hasMany(SolicitacaoExame::class, 'pessoa_id');
    }

    public function convenios()
    {
        return $this->belongsToMany(Convenio::class, 'convenio_medico_tuss', 'pessoa_id', 'convenio_id')
            ->distinct();
    }

    public function convenioTuss()
    {
        return $this->belongsToMany(Tuss::class, 'convenio_medico_tuss', 'pessoa_id', 'tuss_id')
            ->withPivot('convenio_id')
            ->withTimestamps();
    }

    public function salas()
    {
        return $this->hasMany(Sala::class, 'pessoa_id');
    }
}
