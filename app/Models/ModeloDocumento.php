<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModeloDocumento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'modelos_documentos';

    protected $fillable = [
        'tipo',
        'nome',
        'conteudo_template',
        'ativo',
    ];

    public function documentos()
    {
        return $this->hasMany(DocumentoProntuario::class, 'modelo_documento_id');
    }
}
