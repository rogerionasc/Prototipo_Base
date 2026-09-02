<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\SoftDeletes;

class Atendimento extends Model
{
    use BelongsToAccount;
    use HasFactory, SoftDeletes;
    use \App\Traits\BelongsToAccount;

    protected $table = 'atendimentos';

    protected $fillable = [
        'sessao',
        'paciente_id',
        'convenio_id',
        'medico_id',
        'agendamento_id',
        'autorizacao_id',
        'guia_id',
        'caixa_pagamento_id',
        'procedimento_id',
        'tuss_id',
        'categoria_procedimento_id',
        'tipo_atendimento',
        'data_atendimento',
        'hora_prevista',
        'hora_inicio',
        'hora_fim',
        'prioridade',
        'status',
        'emergencia',
        'observacao',
        'motivo_cancelamento',
        'criado_por',
        'atualizado_por'
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function convenio()
    {
        return $this->belongsTo(Convenio::class, 'convenio_id');
    }

    public function medico()
    {
        return $this->belongsTo(Pessoa::class, 'medico_id');
    }

    public function agendamento()
    {
        return $this->belongsTo(Agendamento::class, 'agendamento_id');
    }

    public function caixaPagamento()
    {
        return $this->belongsTo(Pagamento::class, 'caixa_pagamento_id');
    }

    public function procedimento()
    {
        return $this->belongsTo(Procedimento::class, 'procedimento_id');
    }

    public function tuss()
    {
        return $this->belongsTo(Tuss::class, 'tuss_id');
    }

    public function categoriaProcedimento()
    {
        return $this->belongsTo(CategoriaProcedimento::class, 'categoria_procedimento_id');
    }

    public function criadoPor()
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public function atualizadoPor()
    {
        return $this->belongsTo(User::class, 'atualizado_por');
    }

    public function guia()
    {
        return $this->belongsTo(Guia::class, 'guia_id');
    }

    public function pep()
    {
        return $this->hasOne(Pep::class, 'atendimento_id');
    }

    public function convenioTussPivot()
    {
        return $this->hasOne(ConvenioTuss::class, 'tuss_id', 'tuss_id')
            ->where('convenio_id', $this->convenio_id);
    }
}
