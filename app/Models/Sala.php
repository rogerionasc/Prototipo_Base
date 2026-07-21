<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Sala extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nome',
        'status',
        'profissional_saude_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function profissionalSaude()
    {
        return $this->belongsTo(\App\Models\ProfissionalSaude::class, 'profissional_saude_id');
    }
}
