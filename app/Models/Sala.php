<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Sala extends Model
{
    use HasFactory, SoftDeletes;
    use \App\Traits\BelongsToAccount;

    protected $fillable = [
        'nome',
        'status',
        'pessoa_id',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function profissionalSaude()
    {
        return $this->belongsTo(\App\Models\Pessoa::class, 'pessoa_id');
    }
}
