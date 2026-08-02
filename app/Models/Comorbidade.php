<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comorbidade extends Model
{
    protected $table = 'comorbidades';

    protected $fillable = ['nome'];

    public function pacientes()
    {
        return $this->belongsToMany(Paciente::class, 'comorbidade_paciente', 'comorbidade_id', 'paciente_id');
    }
}
