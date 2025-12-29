<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfissionalSaude extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'profissionais_saude';

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
        return $this->belongsToMany(Especialidade::class, 'profissional_especialidade', 'profissional_saude_id', 'especialidade_id')
            ->withPivot('qre');
    }

    public function agendas()
    {
        return $this->hasMany(AgendaMedica::class, 'profissional_saude_id');
    }

    public function atendimentos()
    {
        return $this->hasMany(Atendimento::class, 'profissional_saude_id');
    }

    public function documentosProntuario()
    {
        return $this->hasMany(DocumentoProntuario::class, 'profissional_saude_id');
    }

    public function prescricoes()
    {
        return $this->hasMany(Prescricao::class, 'profissional_saude_id');
    }

    public function solicitacoesExames()
    {
        return $this->hasMany(SolicitacaoExame::class, 'profissional_saude_id');
    }
}
