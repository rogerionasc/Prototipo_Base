<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    use HasFactory;

    protected $table = 'pagamentos';

    protected $fillable = [
        'orcamento_id',
        'caixa_id',
        'movimentacao_id',
        'valor',
        'forma_pagamento',
        'data_pagamento',
        'confirmado',
        'status',
        'recusa_justificativa',
        'recusado_por',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'confirmado' => 'boolean',
        'data_pagamento' => 'datetime',
        'recusado_por' => 'integer',
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class, 'orcamento_id');
    }

    public function caixa()
    {
        return $this->belongsTo(Caixa::class, 'caixa_id');
    }

    public function movimentacao()
    {
        return $this->belongsTo(MovimentacaoCaixa::class, 'movimentacao_id');
    }
}
