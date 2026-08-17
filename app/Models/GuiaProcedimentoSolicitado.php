<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuiaProcedimentoSolicitado extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function guia()
    {
        return $this->belongsTo(Guia::class);
    }

    public function procedimentosRealizados()
    {
        return $this->hasMany(GuiaProcedimentoRealizado::class, 'procedimento_solicitado_id');
    }

    public function autorizacoes()
    {
        return $this->hasMany(Autorizacao::class, 'procedimento_solicitado_id');
    }
}
