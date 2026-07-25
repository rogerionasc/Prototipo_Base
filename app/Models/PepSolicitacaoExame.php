<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PepSolicitacaoExame extends Model
{
    use HasFactory;

    protected $table = 'pep_solicitacoes_exames';

    protected $fillable = [
        'pep_id',
        'procedimento_id',
        'justificativa',
        'urgente',
        'status',
        'profissional_id'
    ];

    public function pep()
    {
        return $this->belongsTo(Pep::class, 'pep_id');
    }
}
