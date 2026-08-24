<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\SoftDeletes;

class Autorizacao extends Model
{
    use BelongsToAccount;
    use HasFactory, SoftDeletes;
    use \App\Traits\BelongsToAccount;

    protected $table = 'autorizacoes';

    protected $fillable = [
        'protocolo',
        'convenio_id',
        'guia_id',
        'procedimento_solicitado_id',
        'tuss_id',
        'valor',
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

    public function guia()
    {
        return $this->belongsTo(Guia::class, 'guia_id');
    }

    public function procedimentoSolicitado()
    {
        return $this->belongsTo(GuiaProcedimentoSolicitado::class, 'procedimento_solicitado_id');
    }
}
