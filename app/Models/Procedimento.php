<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procedimento extends Model
{
    use HasFactory;

    protected $table = 'procedimentos';

    protected $fillable = [
        'nome',
        'descricao',
        'valor',
    ];

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class, 'procedimento_id');
    }
}
