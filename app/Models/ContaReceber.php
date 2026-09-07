<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaReceber extends Model
{
    use HasFactory, \App\Traits\BelongsToAccount;

    protected $table = 'contas_receber';

    protected $fillable = [
        'account_id',
        'faturamento_id',
        'paciente_id',
        'convenio_id',
        'valor',
        'vencimento',
        'status',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'vencimento' => 'date',
    ];

    public function faturamento()
    {
        return $this->belongsTo(Faturamento::class);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function convenio()
    {
        return $this->belongsTo(Convenio::class);
    }

    public function cobrancas()
    {
        return $this->hasMany(Cobranca::class, 'conta_receber_id');
    }
}
