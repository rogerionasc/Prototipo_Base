<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToAccount;

class Caixa extends Model
{
    use BelongsToAccount;
    use HasFactory;
    use \App\Traits\BelongsToAccount;

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
