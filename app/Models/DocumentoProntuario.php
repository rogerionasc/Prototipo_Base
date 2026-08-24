<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoProntuario extends Model
{
    use BelongsToAccount;
    use HasFactory, SoftDeletes;
    use \App\Traits\BelongsToAccount;

    protected $table = 'documentos_prontuario';

    protected $fillable = [
        'prontuario_id',
        'modelo_documento_id',
        'pessoa_id',
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
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }
}
