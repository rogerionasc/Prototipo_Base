<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PepPrescricao extends Model
{
    use HasFactory;

    protected $table = 'pep_prescricoes';

    protected $fillable = [
        'pep_id',
        'profissional_id',
        'observacao',
        'validade'
    ];

    public function pep()
    {
        return $this->belongsTo(Pep::class, 'pep_id');
    }

    public function itens()
    {
        return $this->hasMany(PepPrescricaoItem::class, 'prescricao_id');
    }

    public function profissional()
    {
        return $this->belongsTo(ProfissionalSaude::class, 'profissional_id');
    }
}
