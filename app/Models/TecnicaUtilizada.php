<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TecnicaUtilizada extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'descricao',
        'ativo',
    ];
}
