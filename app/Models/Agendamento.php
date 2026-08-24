<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agendamento extends Model
{
    use BelongsToAccount;
    use HasFactory, SoftDeletes, \App\Traits\BelongsToAccount;

    protected $table = 'agendamentos';

    protected $fillable = [
        'agenda_medica_id',
        'data',
        'hora',
        'paciente_id',
        'procedimento_id',
        'tuss_id',
        'sessao_tratamento_id',

        'status_id',
        'agendamento_origem_id',
        'valor_cobrado',
        'observacoes',
        'emergencia',
        'convenio_id',
    ];

    public function agendaMedica()
    {
        return $this->belongsTo(AgendaMedica::class, 'agenda_medica_id');
    }

    public function convenio()
    {
        return $this->belongsTo(Convenio::class, 'convenio_id');
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

    public function guias()
    {
        return $this->hasMany(Guia::class, 'agendamento_id');
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
