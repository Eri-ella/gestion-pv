<?php

namespace Database\Seeders;

use App\Models\AnneeUniversitaire;
use App\Models\Departement;
use App\Models\Filiere;
use App\Models\Niveau;
use Illuminate\Database\Seeder;

class ReferentielSeeder extends Seeder
{
    public function run(): void
    {
        $departements = [
            'Informatique' => 'INFO',
            'Gestion' => 'GEST',
        ];

        foreach ($departements as $libelle => $code) {
            $departement = Departement::firstOrCreate(
                ['code' => $code],
                ['libelle' => $libelle, 'actif' => true],
            );

            foreach ($this->filieresFor($code) as [$filiere, $filiereCode]) {
                Filiere::firstOrCreate(
                    ['code' => $filiereCode],
                    ['departement_id' => $departement->id, 'libelle' => $filiere],
                );
            }
        }

        foreach ([
            ['libelle' => '2024-2025', 'courante' => false],
            ['libelle' => '2025-2026', 'courante' => true],
        ] as $annee) {
            AnneeUniversitaire::firstOrCreate(['libelle' => $annee['libelle']], $annee);
        }

        foreach ([
            ['libelle' => 'Licence 1', 'ordre' => 1],
            ['libelle' => 'Licence 2', 'ordre' => 2],
            ['libelle' => 'Licence 3', 'ordre' => 3],
        ] as $niveau) {
            Niveau::firstOrCreate(['libelle' => $niveau['libelle']], $niveau);
        }
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    private function filieresFor(string $departementCode): array
    {
        return match ($departementCode) {
            'INFO' => [
                ['Informatique de gestion', 'IG'],
                ['Systèmes informatiques', 'SI'],
            ],
            'GEST' => [
                ['Comptabilité et gestion', 'CG'],
                ['Marketing et communication', 'MC'],
            ],
        };
    }
}
