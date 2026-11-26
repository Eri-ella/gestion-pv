<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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
    }
}
