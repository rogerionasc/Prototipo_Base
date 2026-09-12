<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PixConfig extends Model
{
    protected $fillable = [
        'account_id',
        'pix_chave',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
