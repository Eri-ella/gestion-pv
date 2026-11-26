<?php

namespace Database\Seeders;

use App\Models\AnneeUniversitaire;
use App\Models\Promotion;
use App\Models\Pv;
use App\Models\User;
use Illuminate\Database\Seeder;

class PvSeeder extends Seeder
{
    public function run(): void
    {
        $annee = AnneeUniversitaire::where('courante', true)->firstOrFail();
        $agentId = User::where('email', 'scolarite@eneam.local')->firstOrFail()->id;
        $adminId = User::where('email', 'admin@eneam.local')->firstOrFail()->id;
        $promotions = Promotion::where('annee_universitaire_id', $annee->id)
            ->orderBy('id')
            ->take(4)
            ->with(['filiere', 'niveau'])
            ->get();
        $statuts = ['brouillon', 'brouillon', 'valide', 'archive'];

        foreach ($promotions as $index => $promotion) {
            $statut = $statuts[$index];
            $reference = sprintf('PV-%s-%s-%s', $annee->libelle, $promotion->filiere->code, $promotion->niveau->ordre);

            Pv::updateOrCreate(
                ['promotion_id' => $promotion->id],
                [
                    'reference' => $reference,
                    'statut' => $statut,
                    'date_deliberation' => $annee->courante ? '2026-06-15' : '2025-06-15',
                    'president_jury' => 'Professeur '.$promotion->filiere->libelle,
                    'cree_par' => $agentId,
                    'valide_par' => $statut === 'brouillon' ? null : $adminId,
                    'date_validation' => $statut === 'brouillon' ? null : now(),
                ],
            );
        }
    }
}
