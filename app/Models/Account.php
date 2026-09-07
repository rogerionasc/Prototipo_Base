<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cnpj',
        'cnes',
        'endereco',
        'telefone',
        'email',
        'ativo'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function totens()
    {
        return $this->hasMany(Totem::class);
    }

    public function paineis()
    {
        return $this->hasMany(Painel::class);
    }

    public function salas()
    {
        return $this->hasMany(Sala::class);
    }

    public function guiches()
    {
        return $this->hasMany(Guiche::class);
    }

    public function configuracoesBancarias()
    {
        return $this->hasMany(ConfiguracaoBancaria::class, 'account_id');
    }

    public function setCnpjAttribute($value)
    {
        $this->attributes['cnpj'] = preg_replace('/[^0-9]/', '', $value);
    }

    public function getCnpjAttribute($value)
    {
        if (!$value) return $value;
        $cnpj = preg_replace('/[^0-9]/', '', $value);
        if (strlen($cnpj) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
        }
        return $value;
    }
}
