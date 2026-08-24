<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToAccount;

class Guiche extends Model
{
    use BelongsToAccount;
    protected $table = 'guiches';
    protected $fillable = ['nome', 'hostname', 'status'];
    protected $casts = ['status' => 'boolean'];
}
