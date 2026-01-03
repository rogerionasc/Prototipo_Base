<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrcamentoProcedimento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orcamento_procedimentos';

    protected $fillable = [
        'orcamento_id',
        'procedimento_id',
        'quantidade',
        'valor_unitario',
        'valor_total',
        'observacoes',
    ];

    protected $casts = [
        'valor_unitario' => 'decimal:2',
        'valor_total' => 'decimal:2',
    ];

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class, 'orcamento_id');
    }

    public function procedimento()
    {
        return $this->belongsTo(Procedimento::class, 'procedimento_id');
    }
}

