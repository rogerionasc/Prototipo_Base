<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Endereco extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'enderecos';

    protected $fillable = [
        'cep',
        'endereco',
        'numero',
        'bairro',
        'cidade',
        'complemento',
    ];

    public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'endereco_id');
    }

    public function responsaveis()
    {
        return $this->hasMany(Responsavel::class, 'endereco_id');
    }

    public function profissionaisSaude()
    {
        return $this->hasMany(ProfissionalSaude::class, 'endereco_id');
    }
}
