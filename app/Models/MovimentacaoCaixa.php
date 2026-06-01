<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoCaixa extends Model
{
    use HasFactory;

    protected $table = 'movimentacoes_caixa';

    protected $fillable = [
        'caixa_id',
        'aberto_por_id',
        'numero',
        'data_movimento',
        'total_entradas',
        'total_saidas',
        'saldo_caixa',
        'total_entrada_prazo',
        'total_saida_prazo',
        'total_transferencia',
        'total_conferencia',
        'saldo_movimento',
        'valor_diferenca',
        'observacoes_fechamento',
        'fechado_em',
        'fechado_por_id',
        'reaberto_por_id',
    ];

    protected $casts = [
        'data_movimento' => 'date:Y-m-d',
        'total_entradas' => 'decimal:2',
        'total_saidas' => 'decimal:2',
        'saldo_caixa' => 'decimal:2',
        'total_entrada_prazo' => 'decimal:2',
        'total_saida_prazo' => 'decimal:2',
        'total_transferencia' => 'decimal:2',
        'total_conferencia' => 'decimal:2',
        'saldo_movimento' => 'decimal:2',
        'valor_diferenca' => 'decimal:2',
        'fechado_em' => 'datetime',
        'aberto_por_id' => 'integer',
        'fechado_por_id' => 'integer',
        'reaberto_por_id' => 'integer',
    ];

    public function caixa()
    {
        return $this->belongsTo(Caixa::class, 'caixa_id');
    }
}
