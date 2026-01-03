<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caixa extends Model
{
    use HasFactory;

    protected $table = 'caixas';

    protected $fillable = [
        'descricao',
        'tipo',
        'bloquear_receber',
        'bloquear_pagar',
        'ativo',
    ];

    protected $casts = [
        'bloquear_receber' => 'boolean',
        'bloquear_pagar' => 'boolean',
        'ativo' => 'boolean',
    ];
}
