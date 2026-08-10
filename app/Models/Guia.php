<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guia extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $guarded = ['id'];

    public function faturamento()
    {
        return $this->belongsTo(Faturamento::class, 'faturamento_id');
    }

    public function atendimento()
    {
        return $this->hasOne(Atendimento::class, 'guia_id');
    }
}
