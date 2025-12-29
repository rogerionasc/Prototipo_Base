<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaProcedimento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categorias_procedimento';

    protected $fillable = [
        'nome',
    ];

    public function procedimentos()
    {
        return $this->hasMany(Procedimento::class, 'categoria_id');
    }
}

