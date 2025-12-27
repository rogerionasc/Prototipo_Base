<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricoProntuario extends Model
{
    use HasFactory;

    protected $table = 'historico_prontuario';

    protected $fillable = [
        'atendimento_id',
        'data_registro',
        'descricao',
    ];

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class, 'atendimento_id');
    }
}
