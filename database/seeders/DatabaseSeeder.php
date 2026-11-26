<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (['administrateur', 'chef_departement', 'agent_scolarite'] as $libelle) {
            Role::firstOrCreate(['libelle' => $libelle]);
        }

        User::updateOrCreate(
            ['email' => 'kirua@gmail.com'],
            ['name' => 'kirua', 'password' => '123', 'actif' => true,
            'role_id' => Role::firstOrCreate(['libelle' => 'administrateur'])->id]
        );
    }
}
