<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PepPrescricaoItem extends Model
{
    use HasFactory;

    protected $table = 'pep_prescricao_itens';

    protected $fillable = [
        'prescricao_id',
        'medicamento_id',
        'dosagem',
        'frequencia',
        'via',
        'duracao',
        'quantidade',
        'observacao'
    ];

    public function prescricao()
    {
        return $this->belongsTo(PepPrescricao::class, 'prescricao_id');
    }
}
