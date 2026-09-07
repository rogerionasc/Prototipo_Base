<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faturamento extends Model
{
    use HasFactory;
    use \App\Traits\BelongsToAccount;

    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $accountId = $model->account_id ?? (auth()->check() ? auth()->user()->account_id : 1);
            if (empty($model->numero_lote)) {
                $datePrefix = date('Ymd');
                
                $lastModel = static::withoutGlobalScopes()
                    ->where('account_id', $accountId)
                    ->where('numero_lote', 'like', "{$datePrefix}{$accountId}%")
                    ->orderBy('id', 'desc')
                    ->first();
                
                $sequence = 1;
                if ($lastModel) {
                    $lastNumberStr = substr($lastModel->numero_lote, -4);
                    $sequence = intval($lastNumberStr) + 1;
                }
                
                $model->numero_lote = sprintf("%s%s%04d", $datePrefix, $accountId, $sequence);
            }
        });
    }

    public function guias()
    {
        return $this->hasMany(Guia::class, 'faturamento_id');
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function convenio()
    {
        return $this->belongsTo(Convenio::class);
    }
}
