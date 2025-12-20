<?php

namespace App\Http\Controllers;

use App\Models\PreCadastro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PreCadastroController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'sobrenome' => 'required|string|max:255',
            'cpf' => 'required|string|max:14|unique:pre_cadastro,cpf',
            'telefone' => 'required|string|max:20',
            'data_nascimento' => 'required|date',
            'email' => 'required|email|max:255|unique:pre_cadastro,email',
            'password' => 'required|string|min:6',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $preCadastro = PreCadastro::create($validated);

        return response()->json(['message' => 'Pré-cadastro realizado com sucesso!', 'pre_cadastro' => $preCadastro], 201);
    }
}
