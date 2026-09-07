<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cobranca extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cobrancas';

    protected $fillable = [
        'account_id',
        'conta_receber_id',
        'configuracao_bancaria_id',
        'gateway',
        'gateway_id',
        'tipo',
        'nosso_numero',
        'linha_digitavel',
        'codigo_barras',
        'pix_txid',
        'url',
        'valor',
        'vencimento',
        'status',
        'data_pagamento',
        'payload',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'vencimento' => 'date',
        'data_pagamento' => 'datetime',
        'payload' => 'array',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function contaReceber()
    {
        return $this->belongsTo(ContaReceber::class, 'conta_receber_id');
    }

    public function configuracaoBancaria()
    {
        return $this->belongsTo(ConfiguracaoBancaria::class, 'configuracao_bancaria_id');
    }
}
