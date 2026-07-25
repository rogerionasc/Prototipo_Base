<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PepReceita extends Model
{
    use HasFactory;

    protected $table = 'pep_receitas';

    protected $fillable = [
        'pep_id',
        'prescricao_id',
        'texto',
        'emitido_em',
        'profissional_id'
    ];

    public function pep()
    {
        return $this->belongsTo(Pep::class, 'pep_id');
    }
}
