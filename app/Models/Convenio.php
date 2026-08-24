<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\SoftDeletes;

class Convenio extends Model
{
    use BelongsToAccount;
    use HasFactory, SoftDeletes;
    use \App\Traits\BelongsToAccount;

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
        'dias_para_faturar',
        'config_spsadt',
    ];

    protected $casts = [
        'config_spsadt' => 'array',
    ];

    public function empresa()
    {
        return $this->belongsTo(Conta::class, 'empresa_id');
    }

    public function medicos()
    {
        return $this->belongsToMany(Pessoa::class, 'convenio_medico_tuss', 'convenio_id', 'pessoa_id')
            ->distinct();
    }

    public function tuss()
    {
        return $this->belongsToMany(Tuss::class, 'convenio_tuss')
            ->withPivot('requer_autorizacao')
            ->withTimestamps();
    }

    public function medicoTuss()
    {
        return $this->belongsToMany(Tuss::class, 'convenio_medico_tuss', 'convenio_id', 'tuss_id')
            ->withPivot('pessoa_id')
            ->withTimestamps();
    }
}
