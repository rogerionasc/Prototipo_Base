<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Painel extends Model
{
    protected $table = 'paineis';
    protected $fillable = ['nome', 'status'];
    protected $casts = ['status' => 'boolean'];
}
