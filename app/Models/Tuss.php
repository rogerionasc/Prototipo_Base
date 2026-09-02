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
        'quantidade_ch',
        'quantidade_co',
        'total',
    ];

    protected $casts = [
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
