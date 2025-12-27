<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescricao extends Model
{
    use HasFactory;

    protected $table = 'prescricoes';

    protected $fillable = [
        'prontuario_id',
        'profissional_saude_id',
        'data_prescricao',
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
