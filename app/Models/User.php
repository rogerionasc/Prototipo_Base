<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // 'name',
        // 'email',
        // 'password',
        'conta_id',
        'account_id',
        'pessoa_id',
        'is_active',
        'email',
        'password',
    ];

    public function conta()
    {
        return $this->belongsTo(Conta::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function profissionalSaude()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id', 'id')->withoutGlobalScope('account');
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id', 'id')->withoutGlobalScope('account');
    }

    public function getNomeAttribute()
    {
        return $this->pessoa ? $this->pessoa->nome : null;
    }

    public function getNameAttribute()
    {
        return $this->nome;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // 'password',
        // 'remember_token',
        // 'two_factor_recovery_codes',
        // 'two_factor_secret',
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        'nome',
        'name'
    ];
}
