<?php

namespace App\Models\Financeiro;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Despesa extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'descricao',
        'valor',
        'categoria_id',
        'data_competencia',
        'data_vencimento',
        'anexo',
        'status',
        'is_recorrente'
    ];

    public function categoria()
    {
        return $this->belongsTo(\App\Models\CategoriaDespesa::class, 'categoria_id');
    }

    public function contasPagar()
    {
        return $this->hasMany(ContaPagar::class, 'despesa_id');
    }
}
