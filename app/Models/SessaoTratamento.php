<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\SoftDeletes;

class SessaoTratamento extends Model
{
    use BelongsToAccount;
    use HasFactory, SoftDeletes;
    use \App\Traits\BelongsToAccount;

    protected $table = 'sessoes_tratamento';

    protected $fillable = [
        'procedimento_id',
        'tuss_id',
        'paciente_id',
        'numero_sessao',
        'data_prevista',
        'realizada',
    ];

    public function procedimento()
    {
        return $this->belongsTo(Procedimento::class, 'procedimento_id');
    }

    public function tuss()
    {
        return $this->belongsTo(Tuss::class, 'tuss_id');
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class, 'sessao_tratamento_id');
    }
}
