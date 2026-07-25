<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PepAssinatura extends Model
{
    use HasFactory;

    protected $table = 'pep_assinaturas';

    protected $fillable = [
        'pep_id',
        'documento_id',
        'profissional_id',
        'tipo_documento',
        'hash_documento',
        'certificado',
        'assinado_em'
    ];

    public function pep()
    {
        return $this->belongsTo(Pep::class, 'pep_id');
    }
}
