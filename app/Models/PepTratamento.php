<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PepTratamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'pep_id',
        'paciente_id',
        'profissional_id',
        'nome_tratamento',
        'quantidade_sessoes_previstas',
        'quantidade_sessoes_realizadas',
        'status',
        'data_inicio',
        'data_fim',
        'observacao',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'quantidade_sessoes_previstas' => 'integer',
        'quantidade_sessoes_realizadas' => 'integer',
    ];

    public function pep()
    {
        return $this->belongsTo(Pep::class);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function profissional()
    {
        return $this->belongsTo(Pessoa::class, 'profissional_id');
    }

    public function evolucoes()
    {
        return $this->hasMany(PepEvolucao::class, 'tratamento_id');
    }
}
