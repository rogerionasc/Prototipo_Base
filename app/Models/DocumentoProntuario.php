<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoProntuario extends Model
{
    use HasFactory;

    protected $table = 'documentos_prontuario';

    protected $fillable = [
        'prontuario_id',
        'modelo_documento_id',
        'profissional_saude_id',
        'data_emissao',
        'conteudo_final',
        'assinado',
    ];

    public function prontuario()
    {
        return $this->belongsTo(Prontuario::class, 'prontuario_id');
    }

    public function modelo()
    {
        return $this->belongsTo(ModeloDocumento::class, 'modelo_documento_id');
    }

    public function profissionalSaude()
    {
        return $this->belongsTo(ProfissionalSaude::class, 'profissional_saude_id');
    }
}
