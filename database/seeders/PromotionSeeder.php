<?php

namespace Database\Seeders;

use App\Models\AnneeUniversitaire;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $annees = AnneeUniversitaire::all();
        $filieres = Filiere::all();
        $niveaux = Niveau::all();

        foreach ($annees as $annee) {
            foreach ($filieres as $filiere) {
                foreach ($niveaux as $niveau) {
                    Promotion::firstOrCreate([
                        'annee_universitaire_id' => $annee->id,
                        'filiere_id' => $filiere->id,
                        'niveau_id' => $niveau->id,
                    ]);
                }
            }
        }
    }
}
