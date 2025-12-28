<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CanalAviso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'canais_aviso';

    protected $fillable = ['nome'];

    public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'canal_aviso_id');
    }
}
