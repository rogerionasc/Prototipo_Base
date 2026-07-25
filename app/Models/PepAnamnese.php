<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PepAnamnese extends Model
{
    use HasFactory;

    protected $table = 'pep_anamneses';

    protected $fillable = [
        'pep_id',
        'queixa_principal',
        'historia_doenca_atual',
        'antecedentes_pessoais',
        'antecedentes_familiares',
        'historico_social',
        'alergias',
        'medicamentos_uso',
        'habitos_vida',
        'observacao',
        'created_by',
        'updated_by'
    ];

    public function pep()
    {
        return $this->belongsTo(Pep::class, 'pep_id');
    }
}
