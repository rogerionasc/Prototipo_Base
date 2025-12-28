<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoSanguineo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tipo_sanguineo';

    protected $fillable = ['descricao'];

    public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'tipo_sanguineo_id');
    }
}
