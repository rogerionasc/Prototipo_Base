<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Autorizacao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'autorizacoes';

    protected $fillable = [
        'protocolo',
        'convenio_id',
        'agendamento_id',
        'tuss_id',
        'valor',
        'carteira',
        'numero_autorizacao',
        'status',
        'validade',
        'data_solicitacao',
        'data_resposta',
        'observacao',
        'usuario_id',
        'usuario_id_validou',
    ];

    protected $casts = [
        'validade' => 'date',
        'data_solicitacao' => 'datetime',
        'data_resposta' => 'datetime',
    ];

    public function convenio()
    {
        return $this->belongsTo(Convenio::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function usuarioValidou()
    {
        return $this->belongsTo(User::class, 'usuario_id_validou');
    }

    public function tuss()
    {
        return $this->belongsTo(Tuss::class, 'tuss_id');
    }

    public function agendamento()
    {
        return $this->belongsTo(Agendamento::class, 'agendamento_id');
    }
}
