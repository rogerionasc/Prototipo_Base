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
        return $this->belongsToMany(Pessoa::class, 'profissional_especialidade', 'especialidade_id', 'pessoa_id')
            ->withPivot('qre');
    }

    public function atendimentos()
    {
        return $this->hasMany(Atendimento::class, 'especialidade_id');
    }

    public function procedimentos()
    {
        return $this->belongsToMany(Procedimento::class, 'especialidade_procedimento', 'especialidade_id', 'procedimento_id');
    }
}
