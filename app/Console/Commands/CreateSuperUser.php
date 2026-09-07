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

        $account = \App\Models\Account::where('cnpj', '00000000000000')->first();
        if (!$account) {
            $account = new \App\Models\Account();
            $account->id = 1;
            $account->cnpj = '00000000000000';
            $account->name = 'Clínica Matriz';
            $account->cnes = '0000000';
            $account->save();
        }

        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            if (!$existingUser->account_id) {
                $existingUser->account_id = $account->id;
                $existingUser->save();
                $this->info("Usuário {$email} já existia. A Clínica Matriz foi vinculada a ele para liberar o acesso.");
            } else {
                $this->error("O usuário com email {$email} já existe.");
            }
            return;
        }

        $pessoa = new Pessoa();
        $pessoa->id = 1;
        $pessoa->nome = 'Super Usuário';
        $pessoa->cpf = '000.000.000-00';
        $pessoa->telefone = '11999999999';
        $pessoa->data_nascimento = '2000-01-01';
        $pessoa->email = $email;
        $pessoa->account_id = $account->id;
        $pessoa->save();


        $user = new User();
        $user->id = 1;
        $user->pessoa_id = $pessoa->id;
        $user->email = $email;
        $user->password = Hash::make('12345678');
        $user->account_id = $account->id;
        $user->save();
        $user->email_verified_at = now();

        $role = Role::firstOrCreate(['name' => 'super-admin']);
        $role->syncPermissions(Permission::all());
        $user->assignRole($role);
        $user->save();

        $this->info('Super usuário criado com sucesso!');
    }
}
