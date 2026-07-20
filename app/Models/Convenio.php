<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Convenio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'convenios';

    protected $fillable = [
        'descricao',
        'logo_path',
        'tuss_tabela',
        'tipo',
        'empresa_id',
        'ans',
        'dias_recebimento',
        'dias_retorno',
    ];

    public function empresa()
    {
        return $this->belongsTo(Conta::class, 'empresa_id');
    }

    public function medicos()
    {
        return $this->belongsToMany(ProfissionalSaude::class, 'convenio_medico_tuss', 'convenio_id', 'profissional_saude_id')
            ->distinct();
    }

    public function tuss()
    {
        return $this->belongsToMany(Tuss::class, 'convenio_tuss')
            ->withTimestamps();
    }

    public function medicoTuss()
    {
        return $this->belongsToMany(Tuss::class, 'convenio_medico_tuss', 'convenio_id', 'tuss_id')
            ->withPivot('profissional_saude_id')
            ->withTimestamps();
    }
}
