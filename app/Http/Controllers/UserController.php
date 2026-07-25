<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\ProfissionalSaude;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with('profissionalSaude')->get();
        $profissionais = ProfissionalSaude::all();

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'profissionais' => $profissionais,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'sobrenome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'cpf' => ['required', 'string', 'max:14', 'unique:users'],
            'telefone' => ['required', 'string', 'max:15'],
            'data_nascimento' => ['required', 'date'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'profissional_saude_id' => ['nullable', 'exists:profissionais_saude,id'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('usuarios.index')->with('success', 'Usuário criado com sucesso!');
    }
}
