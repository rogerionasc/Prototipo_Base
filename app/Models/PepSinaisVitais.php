<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PepSinaisVitais extends Model
{
    use HasFactory;

    protected $table = 'pep_sinais_vitais';

    protected $fillable = [
        'pep_id',
        'pressao_sistolica',
        'pressao_diastolica',
        'frequencia_cardiaca',
        'frequencia_respiratoria',
        'temperatura',
        'saturacao',
        'peso',
        'altura',
        'imc',
        'glicemia',
        'circunferencia_abdominal',
        'observacao',
        'profissional_id'
    ];

    public function pep()
    {
        return $this->belongsTo(Pep::class, 'pep_id');
    }
}
