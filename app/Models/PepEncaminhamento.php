<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PepEncaminhamento extends Model
{
    use HasFactory;

    protected $table = 'pep_encaminhamentos';

    protected $fillable = [
        'pep_id',
        'especialidade_destino',
        'profissional_destino',
        'motivo',
        'observacao',
        'profissional_id'
    ];

    public function pep()
    {
        return $this->belongsTo(Pep::class, 'pep_id');
    }
}
