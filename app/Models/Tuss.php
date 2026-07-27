<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tuss extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tuss';

    protected $fillable = [
        'tabela',
        'codigo',
        'descricao',
        'm2_filme',
        'auxiliares',
        'incidencia',
        'porte',
        'ch',
        'co',
        'total',
        'eh_tratamento',
        'quantidade_sessoes',
    ];

    protected $casts = [
        'eh_tratamento' => 'boolean',
    ];

    public function convenios()
    {
        return $this->belongsToMany(Convenio::class, 'convenio_tuss')
            ->withPivot('requer_autorizacao')
            ->withTimestamps();
    }

    public function medicoConvenios()
    {
        return $this->belongsToMany(Pessoa::class, 'convenio_medico_tuss', 'tuss_id', 'pessoa_id')
            ->withPivot('convenio_id')
            ->withTimestamps();
    }
}
