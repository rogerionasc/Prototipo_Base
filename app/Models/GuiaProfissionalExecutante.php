<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuiaProfissionalExecutante extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function procedimentoRealizado()
    {
        return $this->belongsTo(GuiaProcedimentoRealizado::class, 'procedimento_realizado_id');
    }
}
