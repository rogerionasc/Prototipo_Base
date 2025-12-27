<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes';

    protected $fillable = [
        'nome',
        'cpf',
        'rg',
        'sexo',
        'data_nascimento',
        'naturalidade',
        'estado_civil_id',
        'convenio',
        'altura',
        'peso',
        'cor_pele',
        'endereco_id',
        'receber_avisos',
        'celular',
        'telefone',
        'email',
        'canal_aviso_id',
        'profissao',
        'escolaridade',
        'nome_mae',
        'nome_pai',
        'tipo_sanguineo_id',
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

    public function canalAviso()
    {
        return $this->belongsTo(CanalAviso::class, 'canal_aviso_id');
    }

    public function tipoSanguineo()
    {
        return $this->belongsTo(TipoSanguineo::class, 'tipo_sanguineo_id');
    }

    public function prontuario()
    {
        return $this->hasOne(Prontuario::class, 'paciente_id');
    }

    public function responsaveis()
    {
        return $this->belongsToMany(Responsavel::class, 'paciente_responsavel', 'paciente_id', 'responsavel_id');
    }
}
