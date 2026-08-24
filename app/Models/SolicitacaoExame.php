<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitacaoExame extends Model
{
    use BelongsToAccount;
    use HasFactory, SoftDeletes;
    use \App\Traits\BelongsToAccount;

    protected $table = 'solicitacao_exames';

    protected $fillable = [
        'prontuario_id',
        'pessoa_id',
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
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
