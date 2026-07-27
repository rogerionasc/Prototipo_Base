<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PepDiagnostico extends Model
{
    use HasFactory;

    protected $table = 'pep_diagnosticos';

    protected $fillable = [
        'pep_id',
        'cid_id',
        'principal',
        'descricao',
        'confirmado',
        'profissional_id'
    ];

    public function pep()
    {
        return $this->belongsTo(Pep::class, 'pep_id');
    }

    public function cid()
    {
        return $this->belongsTo(Cid::class, 'cid_id');
    }
    
    public function profissional()
    {
        return $this->belongsTo(Pessoa::class, 'profissional_id');
    }
}
