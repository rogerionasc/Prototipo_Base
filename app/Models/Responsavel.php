<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Responsavel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'responsaveis';

    protected $fillable = [
        'nome',
        'parentesco_id',
        'cpf',
        'rg',
        'data_nascimento',
        'endereco_id',
        'celular',
        'telefone',
        'email',
    ];

    public function parentesco()
    {
        return $this->belongsTo(Parentesco::class, 'parentesco_id');
    }

    public function endereco()
    {
        return $this->belongsTo(Endereco::class, 'endereco_id');
    }

    public function pacientes()
    {
        return $this->belongsToMany(Paciente::class, 'paciente_responsavel', 'responsavel_id', 'paciente_id');
    }
}
