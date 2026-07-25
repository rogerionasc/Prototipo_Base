<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PepExameFisico extends Model
{
    use HasFactory;

    protected $table = 'pep_exames_fisicos';

    protected $fillable = [
        'pep_id',
        'descricao',
        'observacao',
        'profissional_id'
    ];

    public function pep()
    {
        return $this->belongsTo(Pep::class, 'pep_id');
    }
}
