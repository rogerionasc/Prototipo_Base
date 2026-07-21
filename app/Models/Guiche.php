<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guiche extends Model
{
    protected $table = 'guiches';
    protected $fillable = ['nome', 'hostname', 'status'];
    protected $casts = ['status' => 'boolean'];
}
