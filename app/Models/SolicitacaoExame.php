<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitacaoExame extends Model
{
    use HasFactory;

    protected $table = 'solicitacao_exames';

    protected $fillable = [
        'prontuario_id',
        'profissional_saude_id',
        'prescricao',
        'observacoes',
        'ativa',
    ];

    public function prontuario()
    {
        return $this->belongsTo(Prontuario::class, 'prontuario_id');
    }

    public function profissionalSaude()
    {
        return $this->belongsTo(ProfissionalSaude::class, 'profissional_saude_id');
    }
}
