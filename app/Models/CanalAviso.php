<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CanalAviso extends Model
{
    use HasFactory;

    protected $table = 'canais_aviso';

    protected $fillable = ['nome'];

    public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'canal_aviso_id');
    }
}
