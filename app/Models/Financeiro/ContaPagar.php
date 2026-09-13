<?php

namespace App\Models\Financeiro;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaPagar extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'despesa_id',
        'descricao',
        'valor',
        'categoria_id',
        'data_competencia',
        'data_vencimento',
        'anexo',
        'status',
        'data_pagamento',
        'valor_pago',
        'configuracao_bancaria_id'
    ];

    public function categoria()
    {
        return $this->belongsTo(\App\Models\CategoriaDespesa::class, 'categoria_id');
    }

    public function despesa()
    {
        return $this->belongsTo(Despesa::class, 'despesa_id');
    }

    public function configuracaoBancaria()
    {
        return $this->belongsTo(\App\Models\ConfiguracaoBancaria::class, 'configuracao_bancaria_id');
    }
}
