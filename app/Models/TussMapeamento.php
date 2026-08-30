<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToAccount;

class TussMapeamento extends Model
{
    use HasFactory, SoftDeletes, BelongsToAccount;

    protected $table = 'tuss_mapeamentos';

    protected $fillable = [
        'account_id',
        'origem_procedimento_id',
        'referencia_procedimento_id',
        'observacao',
    ];

    public function origemProcedimento()
    {
        return $this->belongsTo(Tuss::class, 'origem_procedimento_id');
    }

    public function referenciaProcedimento()
    {
        return $this->belongsTo(Tuss::class, 'referencia_procedimento_id');
    }
}
