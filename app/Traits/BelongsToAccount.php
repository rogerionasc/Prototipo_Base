<?php

namespace App\Traits;

use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToAccount
{
    protected static function bootBelongsToAccount()
    {
        // 1. Filtro Automático (Global Scope)
        static::addGlobalScope('account', function (Builder $builder) {
            if (app()->runningInConsole()) {
                return; // Console/Tinker tem acesso total
            }

            if (auth()->check()) {
                $accountId = session('current_account_id', auth()->user()->account_id);
                if ($accountId) {
                    $builder->where($builder->getQuery()->from . '.account_id', $accountId);
                } else {
                    $builder->whereRaw('0 = 1'); // Autenticado, mas sem conta
                }
            } else {
                $builder->whereRaw('0 = 1'); // Não autenticado: bloqueia
            }
        });

        // 2. Preenchimento Automático (Creating) e Bloqueio
        static::creating(function ($model) {
            if (auth()->check() && !app()->runningInConsole()) {
                if (!$model->account_id) {
                    if (request()->has('account_id')) {
                        $model->account_id = request('account_id');
                    } else {
                        $accountId = session('current_account_id', auth()->user()->account_id);
                        if ($accountId) {
                            $model->account_id = $accountId;
                        } else {
                            throw new \Exception("Acesso Negado: O usuário não está associado a nenhuma Clínica (Account). Nenhuma ação pode ser executada.");
                        }
                    }
                }
            }
        });
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
