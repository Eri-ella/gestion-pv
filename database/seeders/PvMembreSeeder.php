<?php

namespace Database\Seeders;

use App\Models\Pv;
use App\Models\PvMembre;
use Illuminate\Database\Seeder;

class PvMembreSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Pv::all() as $pv) {
            foreach ([
                ['nom' => 'Professeur de département', 'fonction' => 'Président', 'president' => true],
                ['nom' => 'Représentant de la scolarité', 'fonction' => 'Secrétaire', 'president' => false],
            ] as $membre) {
                PvMembre::updateOrCreate(
                    ['pv_id' => $pv->id, 'nom' => $membre['nom']],
                    ['fonction' => $membre['fonction'], 'president' => $membre['president']],
                );
            }
        }
    }
}
