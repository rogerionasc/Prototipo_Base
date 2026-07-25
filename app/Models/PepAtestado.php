<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PepAtestado extends Model
{
    use HasFactory;

    protected $table = 'pep_atestados';

    protected $fillable = [
        'pep_id',
        'dias',
        'cid_id',
        'texto',
        'emitido_em',
        'profissional_id'
    ];

    public function pep()
    {
        return $this->belongsTo(Pep::class, 'pep_id');
    }
}
