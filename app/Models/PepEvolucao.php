<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PepEvolucao extends Model
{
    use HasFactory;

    protected $table = 'pep_evolucoes';

    protected $fillable = [
        'pep_id',
        'tratamento_id',
        'profissional_id',
        'tipo',
        'descricao'
    ];

    public function pep()
    {
        return $this->belongsTo(Pep::class, 'pep_id');
    }

    public function tratamento()
    {
        return $this->belongsTo(PepTratamento::class, 'tratamento_id');
    }

    public function profissional()
    {
        return $this->belongsTo(Pessoa::class, 'profissional_id');
    }
}
