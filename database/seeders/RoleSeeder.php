<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['administrateur', 'chef_departement', 'agent_scolarite'] as $libelle) {
            Role::firstOrCreate(['libelle' => $libelle]);
        }
    }
}
