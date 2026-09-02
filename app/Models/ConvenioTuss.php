<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConvenioTuss extends Model
{
    use HasFactory;

    protected $table = 'convenio_tuss';

    protected $fillable = [
        'account_id',
        'convenio_id',
        'tuss_id',
        'tuss_mapeamento_id',
        'requer_autorizacao',
        'eh_tratamento',
        'quantidade_sessoes',
        'valor_ch',
        'valor_co',
        'valor_procedimento'
    ];

    protected $casts = [
        'eh_tratamento' => 'boolean',
    ];
}
