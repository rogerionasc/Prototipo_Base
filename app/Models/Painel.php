<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToAccount;

class Painel extends Model
{
    use BelongsToAccount;
    protected $table = 'paineis';
    protected $fillable = ['nome', 'status'];
    protected $casts = ['status' => 'boolean'];
}
