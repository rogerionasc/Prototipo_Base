<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfiguracaoBancaria extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'configuracoes_bancarias';

    protected $fillable = [
        'account_id',
        'provedor',
        'tipo',
        'ambiente',
        'numero_convenio',
        'numero_carteira',
        'numero_variacao_carteira',
        'client_id',
        'client_secret',
        'app_key',
        'certificado',
        'ativo',
        'is_padrao',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'client_id' => 'encrypted',
        'client_secret' => 'encrypted',
        'app_key' => 'encrypted',
        'certificado' => 'encrypted',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
