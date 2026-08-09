<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // UserAndPreUserSeeder::class,
            EstadoCivilSeeder::class,
            ParentescoSeeder::class,
            ConselhoSeeder::class,
            TipoSanguineoSeeder::class,
            CanalAvisoSeeder::class,
            CategoriaProcedimentoSeeder::class,
            EspecialidadeSeeder::class,
            ConvenioSeeder::class,
            ProcedimentoSeeder::class,
            PacienteSeeder::class,
            PessoaSeeder::class,
        ]);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

    }
}
