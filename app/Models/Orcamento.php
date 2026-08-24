<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orcamento extends Model
{
    use BelongsToAccount;
    use HasFactory, SoftDeletes;
    use \App\Traits\BelongsToAccount;

    protected $table = 'orcamentos';

    protected $fillable = [
        'numero',
        'data_emissao',
        'validade',
        'convenio_id',
        'paciente_id',
        'valor_bruto',
        'desconto',
        'valor_total',
        'valor_avista',
    ];

    protected $casts = [
        'data_emissao' => 'datetime',
        'validade' => 'date:d-m-Y',
        'valor_bruto' => 'decimal:2',
        'desconto' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'valor_avista' => 'decimal:2',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function convenio()
    {
        return $this->belongsTo(Convenio::class, 'convenio_id');
    }

    public function itens()
    {
        return $this->hasMany(OrcamentoProcedimento::class, 'orcamento_id');
    }
}
