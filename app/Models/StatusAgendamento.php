<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusAgendamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'status_agendamento';

    protected $fillable = ['descricao'];

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class, 'status_id');
    }
}
