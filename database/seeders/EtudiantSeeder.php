<?php

namespace Database\Seeders;

use App\Models\AnneeUniversitaire;
use App\Models\Etudiant;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

class EtudiantSeeder extends Seeder
{
    public function run(): void
    {
        $annee = AnneeUniversitaire::where('courante', true)->firstOrFail();
        $promotions = Promotion::query()
            ->where('annee_universitaire_id', $annee->id)
            ->orderBy('id')
            ->take(4)
            ->get();
        $noms = [
            ['Adjovi', 'Mireille'],
            ['Ahouansou', 'Kevin'],
            ['Akakpo', 'Nadia'],
            ['Amoussou', 'Yann'],
            ['Babatunde', 'Clarisse'],
            ['Dossou', 'Fabrice'],
            ['Gandonou', 'Ruth'],
            ['Hounkpe', 'Maxime'],
            ['Kiki', 'Arielle'],
            ['Lokossou', 'David'],
            ['Mensah', 'Estelle'],
            ['Nago', 'Samuel'],
            ['Orou', 'Chancelle'],
            ['Soglo', 'Ulrich'],
            ['Tchibozo', 'Aïcha'],
        ];

        foreach ($noms as $index => [$nom, $prenoms]) {
            $promotion = $promotions[intdiv($index, 4)] ?? null;

            if ($promotion === null) {
                break;
            }

            $matricule = sprintf('ENEAM-%04d', $index + 1);
            Etudiant::updateOrCreate(
                ['matricule' => $matricule],
                [
                    'promotion_id' => $promotion->id,
                    'nom' => $nom,
                    'prenoms' => $prenoms,
                    'email' => strtolower($matricule).'@etudiant.eneam.local',
                ],
            );
        }
    }
}
