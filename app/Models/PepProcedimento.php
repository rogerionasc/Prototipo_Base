<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PepProcedimento extends Model
{
    use HasFactory;

    protected $table = 'pep_procedimentos';

    protected $fillable = [
        'pep_id',
        'procedimento_id',
        'quantidade',
        'observacao',
        'profissional_id',
        'realizado_em'
    ];

    public function pep()
    {
        return $this->belongsTo(Pep::class, 'pep_id');
    }
}
