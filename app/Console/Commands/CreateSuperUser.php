<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Pessoa;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CreateSuperUser extends Command
{
    protected $signature = 'create:su'; // nome do comando
    protected $description = 'Cria um super usuário com todas as permissões';

    public function handle()
    {
        $email = 'su@sistema.com';

        if (User::where('email', $email)->exists()) {
            $this->error("O usuário com email {$email} já existe.");
            return;
        }

        $pessoa = Pessoa::create([
            'nome' => 'Super Usuário',
            'cpf' => '000.000.000-00',
            'telefone' => '11999999999',
            'data_nascimento' => '2000-01-01',
            'email' => $email,
        ]);

        $user = User::create([
            'pessoa_id' => $pessoa->id,
            'email' => $email,
            'password' => Hash::make('12345678'),
        ]);
        $user->email_verified_at = now();

        $role = Role::firstOrCreate(['name' => 'super-admin']);
        $role->syncPermissions(Permission::all());
        $user->assignRole($role);
        $user->save();

        $this->info('Super usuário criado com sucesso!');
    }
}
