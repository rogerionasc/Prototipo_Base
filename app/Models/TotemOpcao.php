<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TotemOpcao extends Model
{
    protected $table = 'totem_opcoes';
    protected $fillable = [
        'totem_id',
        'nome',
        'codigo',
        'cor',
        'icone',
        'status',
    ];
    protected $casts = ['status' => 'boolean'];

    public function totem()
    {
        return $this->belongsTo(Totem::class);
    }
}
