<?php

namespace Database\Seeders;

use App\Models\Pv;
use App\Models\Resultat;
use Illuminate\Database\Seeder;

class ResultatSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Pv::with('promotion.etudiants')->get() as $pv) {
            foreach ($pv->promotion->etudiants as $index => $etudiant) {
                $moyenne = [15.25, 8.75, 11.50][$index % 3];

                Resultat::updateOrCreate(
                    ['pv_id' => $pv->id, 'etudiant_id' => $etudiant->id],
                    [
                        'moyenne' => $moyenne,
                        'credits_valides' => $moyenne >= 10 ? 60 : 30,
                        'decision' => $moyenne >= 10 ? 'admis' : 'ajourne',
                    ],
                );
            }
        }
    }
}
