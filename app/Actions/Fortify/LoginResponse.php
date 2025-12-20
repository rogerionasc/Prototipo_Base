<?php

namespace App\Actions\Fortify;

// use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        return redirect()->intended(route('dashboard')) // ou outra rota
                         ->with('success', 'Login realizado com sucesso!');
    }
}
