<?php

namespace Database\Seeders;

use App\Models\Departement;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::query()->pluck('id', 'libelle');
        $departement = Departement::where('code', 'INFO')->firstOrFail();

        foreach ([
            [
                'name' => 'Administrateur ENEAM',
                'email' => 'admin@eneam.local',
                'role_id' => $roles['administrateur'],
                'departement_id' => null,
            ],
            [
                'name' => 'Chef du département informatique',
                'email' => 'chef.info@eneam.local',
                'role_id' => $roles['chef_departement'],
                'departement_id' => $departement->id,
            ],
            [
                'name' => 'Agent de scolarité',
                'email' => 'scolarite@eneam.local',
                'role_id' => $roles['agent_scolarite'],
                'departement_id' => null,
            ],
        ] as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [...$user, 'password' => 'password', 'actif' => true],
            );
        }
    }
}
