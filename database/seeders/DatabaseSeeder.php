<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
    }
}
