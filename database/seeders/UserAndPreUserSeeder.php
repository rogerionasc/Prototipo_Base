<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PreCadastro;

class UserAndPreUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuário completo
        User::create([
            'nome' => 'Usuário Teste',
            'sobrenome' => 'Teste',
            'cpf' => '000.000.000-00',
            'telefone' => '11999999999',
            'data_nascimento' => '2000-01-01',
            'email' => 'example@example.com',
            'password' => Hash::make('12345678'),
        ]);

        // Usuário pré-cadastrado
        PreCadastro::create([
            'nome' => 'Pré Usuário',
            'sobrenome' => 'Teste',
            'cpf' => '111.111.111-11',
            'telefone' => '11988888888',
            'data_nascimento' => '2001-02-02',
            'email' => 'preusuario@teste.com',
            'password' => Hash::make('12345678'),
        ]);
    }
}
