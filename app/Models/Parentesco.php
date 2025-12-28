<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parentesco extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parentescos';

    protected $fillable = ['descricao'];

    public function responsaveis()
    {
        return $this->hasMany(Responsavel::class, 'parentesco_id');
    }
}
