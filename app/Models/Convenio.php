<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Convenio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'convenios';

    protected $fillable = [
        'descricao',
        'tipo',
        'empresa_id',
        'ans',
        'dias_recebimento',
        'dias_retorno',
    ];

    public function empresa()
    {
        return $this->belongsTo(Conta::class, 'empresa_id');
    }
}
