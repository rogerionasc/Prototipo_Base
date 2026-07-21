<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Totem extends Model
{
    protected $table = 'totens';
    protected $fillable = ['nome', 'status'];
    protected $casts = ['status' => 'boolean'];

    public function opcoes()
    {
        return $this->hasMany(TotemOpcao::class);
    }
}
