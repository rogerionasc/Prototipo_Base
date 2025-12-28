<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Atendimento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'atendimentos';

    protected $fillable = [
        'agendamento_id',
        'prontuario_id',
        'profissional_saude_id',
        'especialidade_id',
        'data',
        'inicio_atendimento',
        'fim_atendimento',
        'evolucao',
        'cid',
    ];

    public function agendamento()
    {
        return $this->belongsTo(Agendamento::class, 'agendamento_id');
    }

    public function prontuario()
    {
        return $this->belongsTo(Prontuario::class, 'prontuario_id');
    }

    public function profissionalSaude()
    {
        return $this->belongsTo(ProfissionalSaude::class, 'profissional_saude_id');
    }

    public function especialidade()
    {
        return $this->belongsTo(Especialidade::class, 'especialidade_id');
    }

    public function historicos()
    {
        return $this->hasMany(HistoricoProntuario::class, 'atendimento_id');
    }
}
