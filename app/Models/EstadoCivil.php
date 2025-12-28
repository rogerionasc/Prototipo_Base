<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstadoCivil extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'estado_civil';

    protected $fillable = ['descricao'];

    public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'estado_civil_id');
    }

    public function responsaveis()
    {
        return $this->hasMany(Responsavel::class, 'estado_civil_id');
    }

    public function profissionaisSaude()
    {
        return $this->hasMany(ProfissionalSaude::class, 'estado_civil_id');
    }
}
