<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaMedica extends Model
{
    use HasFactory;

    protected $table = 'agenda_medica';

    protected $fillable = [
        'profissional_saude_id',
        'dia_semana',
        'hora_inicio',
        'hora_fim',
    ];

    public function profissionalSaude()
    {
        return $this->belongsTo(ProfissionalSaude::class, 'profissional_saude_id');
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class, 'agenda_medica_id');
    }
}
