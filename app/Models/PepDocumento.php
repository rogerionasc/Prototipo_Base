<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PepDocumento extends Model
{
    use HasFactory;

    protected $table = 'pep_documentos';

    protected $fillable = [
        'pep_id',
        'tipo',
        'titulo',
        'conteudo',
        'emitido_em',
        'profissional_id'
    ];

    public function pep()
    {
        return $this->belongsTo(Pep::class, 'pep_id');
    }
}
