<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Orcamento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orcamentos';

    protected $fillable = [
        'numero',
        'data_emissao',
        'validade',
        'profissional_saude_id',
        'convenio_id',
        'paciente_id',
        'valor_bruto',
        'desconto',
        'valor_total',
        'valor_avista',
        'faturamento_previsto',
        'aprovado',
    ];

    protected $casts = [
        'data_emissao' => 'date:d-m-Y',
        'validade' => 'date:d-m-Y',
        'valor_bruto' => 'decimal:2',
        'desconto' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'valor_avista' => 'decimal:2',
        'faturamento_previsto' => 'boolean',
        'aprovado' => 'boolean',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function profissionalSaude()
    {
        return $this->belongsTo(ProfissionalSaude::class, 'profissional_saude_id');
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
