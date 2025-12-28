<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreCadastro extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pre_cadastro';

    protected $fillable = [
        'nome',
        'sobrenome',
        'cpf',
        'telefone',
        'data_nascimento',
        'email',
        'password',
    ];
}
