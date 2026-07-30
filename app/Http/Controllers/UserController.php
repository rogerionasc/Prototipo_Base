<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Pessoa;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with('profissionalSaude')->get();

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
        ]);
    }

    public function pessoasDisponiveis(Request $request)
    {
        $query = $request->input('q');
        $usuarioAtualId = $request->input('current_pessoa_id');
        
        if (empty($query) && empty($usuarioAtualId)) {
            return response()->json([]);
        }
        
        $usadas = User::whereNotNull('pessoa_id')->pluck('pessoa_id')->toArray();
        
        if ($usuarioAtualId && in_array($usuarioAtualId, $usadas)) {
            $usadas = array_diff($usadas, [$usuarioAtualId]);
        }
        
        $pessoas = Pessoa::whereNotIn('id', $usadas)
            ->when($query, function($q) use ($query) {
                $q->where(function($q2) use ($query) {
                    $q2->where('nome', 'like', "%{$query}%")
                       ->orWhere('cpf', 'like', "%{$query}%")
                       ->orWhere('email', 'like', "%{$query}%");
                });
            })
            ->when(empty($query) && $usuarioAtualId, function($q) use ($usuarioAtualId) {
                $q->where('id', $usuarioAtualId);
            })
            ->limit(50)
            ->get(['id as value', 'nome as label', 'email']);
            
        return response()->json($pessoas);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'pessoa_id' => ['required', 'exists:pessoas,id', 'unique:users,pessoa_id'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('usuarios.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function update(Request $request, User $usuario)
    {
        $rules = [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $usuario->id],
            'pessoa_id' => ['required', 'exists:pessoas,id', 'unique:users,pessoa_id,' . $usuario->id],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Password::defaults()];
        }

        $validated = $request->validate($rules);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $usuario->update($validated);

        return redirect()->route('usuarios.index')->with('success', 'Usuário atualizado com sucesso!');
    }
    public function toggleStatus(User $usuario)
    {
        $usuario->update(['is_active' => !$usuario->is_active]);
        return redirect()->back()->with('success', 'Status do usuário atualizado com sucesso!');
    }
}
