<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Procedimento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'procedimentos';

    protected $fillable = [
        'nome',
        'descricao',
        'categoria_id',
        'eh_tratamento',
        'quantidade_sessoes',
        'valor',
        'comissao_percentual',
        'ativo',
        'valor',
    ];

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class, 'procedimento_id');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaProcedimento::class, 'categoria_id');
    }
}
