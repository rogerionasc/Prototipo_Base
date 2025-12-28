<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prontuario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'prontuarios';

    protected $fillable = [
        'paciente_id',
        'codigo',
        'data_abertura',
        'ativo',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function atendimentos()
    {
        return $this->hasMany(Atendimento::class, 'prontuario_id');
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoProntuario::class, 'prontuario_id');
    }

    public function prescricoes()
    {
        return $this->hasMany(Prescricao::class, 'prontuario_id');
    }

    public function solicitacoesExames()
    {
        return $this->hasMany(SolicitacaoExame::class, 'prontuario_id');
    }
}
