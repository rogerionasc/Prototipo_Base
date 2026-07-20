<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agendamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'agendamentos';

    protected $fillable = [
        'agenda_medica_id',
        'data',
        'hora',
        'paciente_id',
        'procedimento_id',
        'tuss_id',
        'sessao_tratamento_id',
        'orcamento_id',
        'status_id',
        'agendamento_origem_id',
        'valor_cobrado',
        'observacoes',
    ];

    public function agendaMedica()
    {
        return $this->belongsTo(AgendaMedica::class, 'agenda_medica_id');
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function procedimento()
    {
        return $this->belongsTo(Procedimento::class, 'procedimento_id');
    }

    public function status()
    {
        return $this->belongsTo(StatusAgendamento::class, 'status_id');
    }

    public function origem()
    {
        return $this->belongsTo(Agendamento::class, 'agendamento_origem_id');
    }

    public function retornos()
    {
        return $this->hasMany(Agendamento::class, 'agendamento_origem_id');
    }

    public function atendimentos()
    {
        return $this->hasMany(Atendimento::class, 'agendamento_id');
    }

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class, 'orcamento_id');
    }

    public function tuss()
    {
        return $this->belongsTo(Tuss::class, 'tuss_id');
    }

    public function sessaoTratamento()
    {
        return $this->belongsTo(SessaoTratamento::class, 'sessao_tratamento_id');
    }
}
