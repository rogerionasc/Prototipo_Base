<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgendaMedica extends Model
{
    use BelongsToAccount;
    use HasFactory, SoftDeletes;
    use \App\Traits\BelongsToAccount;

    protected $table = 'agenda_medica';

    protected $fillable = [
        'pessoa_id',
        'dia_semana',
        'hora_inicio',
        'hora_fim',
    ];

    public function profissionalSaude()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class, 'agenda_medica_id');
    }
}
