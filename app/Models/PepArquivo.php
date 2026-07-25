<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PepArquivo extends Model
{
    use HasFactory;

    protected $table = 'pep_arquivos';

    protected $fillable = [
        'pep_id',
        'nome',
        'arquivo',
        'mime_type',
        'tamanho',
        'observacao',
        'enviado_por'
    ];

    public function pep()
    {
        return $this->belongsTo(Pep::class, 'pep_id');
    }
}
