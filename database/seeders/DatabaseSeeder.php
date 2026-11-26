<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Promotion;
use App\Models\Etudiant;
use App\Models\Pv;
use App\Models\PvMembre;
use App\Models\Resultat;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ReferentielSeeder::class,
            PromotionSeeder::class,
            UserSeeder::class,
            EtudiantSeeder::class,
            PvSeeder::class,
            PvMembreSeeder::class,
            ResultatSeeder::class,
        ]);

        User::updateOrCreate(
            ['email' => 'kirua@gmail.com'],
            ['name' => 'kirua', 'password' => '123', 'actif' => true,
            'role_id' => Role::firstOrCreate(['libelle' => 'administrateur'])->id]
        );
    }
}
