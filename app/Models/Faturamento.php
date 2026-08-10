<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faturamento extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function guias()
    {
        return $this->hasMany(Guia::class, 'faturamento_id');
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function convenio()
    {
        return $this->belongsTo(Convenio::class);
    }
}
