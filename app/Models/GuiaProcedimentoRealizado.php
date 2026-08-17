<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuiaProcedimentoRealizado extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function guia()
    {
        return $this->belongsTo(Guia::class);
    }

    public function procedimentoSolicitado()
    {
        return $this->belongsTo(GuiaProcedimentoSolicitado::class, 'procedimento_solicitado_id');
    }

    public function profissionaisExecutantes()
    {
        return $this->hasMany(GuiaProfissionalExecutante::class, 'procedimento_realizado_id');
    }
}
