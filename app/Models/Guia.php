<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guia extends Model
{
    use HasFactory, SoftDeletes;
    use \App\Traits\BelongsToAccount;
    
    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $accountId = $model->account_id ?? (auth()->check() ? auth()->user()->account_id : 1);
            if (empty($model->numero_guia_prestador)) {
                $datePrefix = date('Ymd');
                
                $lastModel = static::withoutGlobalScopes()
                    ->where('account_id', $accountId)
                    ->where('numero_guia_prestador', 'like', "{$datePrefix}{$accountId}%")
                    ->orderBy('id', 'desc')
                    ->first();
                
                $sequence = 1;
                if ($lastModel) {
                    $lastNumberStr = substr($lastModel->numero_guia_prestador, -4);
                    $sequence = intval($lastNumberStr) + 1;
                }
                
                $model->numero_guia_prestador = sprintf("%s%s%04d", $datePrefix, $accountId, $sequence);
            }
        });
    }

    public function faturamento()
    {
        return $this->belongsTo(Faturamento::class, 'faturamento_id');
    }

    public function atendimento()
    {
        return $this->hasOne(Atendimento::class, 'guia_id');
    }

    public function agendamento()
    {
        return $this->belongsTo(Agendamento::class, 'agendamento_id');
    }

    public function procedimentosSolicitados()
    {
        return $this->hasMany(GuiaProcedimentoSolicitado::class);
    }

    public function procedimentosRealizados()
    {
        return $this->hasMany(GuiaProcedimentoRealizado::class);
    }

    public function autorizacoes()
    {
        return $this->hasMany(Autorizacao::class);
    }

    public function profissionaisExecutantes()
    {
        return $this->hasManyThrough(GuiaProfissionalExecutante::class, GuiaProcedimentoRealizado::class, 'guia_id', 'procedimento_realizado_id');
    }

    public function guiaOrigem()
    {
        return $this->belongsTo(Guia::class, 'guia_origem_id');
    }

    public function guiasDerivadas()
    {
        return $this->hasMany(Guia::class, 'guia_origem_id');
    }
}
