<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Especialidade extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'especialidades';

    protected $fillable = [
        'nome',
        'codigo',
        'descricao',
        'ativo',
    ];

    public function profissionaisSaude()
    {
        return $this->belongsToMany(ProfissionalSaude::class, 'profissional_especialidade', 'especialidade_id', 'profissional_saude_id');
    }

    public function atendimentos()
    {
        return $this->hasMany(Atendimento::class, 'especialidade_id');
    }
}
