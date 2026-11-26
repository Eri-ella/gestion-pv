<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\Filiere;
use App\Models\Pv;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $pvTotal = Pv::count();
        $pvNumerises = Pv::whereNotNull('fichier_path')->count();
        $pvValides = Pv::where('statut', 'valide')->count();

        $tauxParFiliere = Filiere::withCount('promotions')->get()->map(fn ($filiere) => [
            'filiere' => $filiere->libelle,
            'annee' => '',
            'taux' => $filiere->promotions_count > 0 ? 100 : 0,
        ]);

        return view('dashboard', [
            'stats' => [
                'pv_numerises' => $pvNumerises,
                'pv_attendus' => max(0, $pvTotal - $pvNumerises),
                'pv_total' => $pvTotal,
                'etudiants' => Etudiant::count(),
                'taux_global' => $pvTotal > 0 ? round(($pvValides / $pvTotal) * 100, 1) : 0,
            ],
            'tauxParFiliere' => $tauxParFiliere,
            'pvManquants' => Pv::doesntHave('resultats')->limit(10)->get(['id', 'reference'])
                ->map(fn ($pv) => ['id' => $pv->id, 'libelle' => $pv->reference]),
        ]);
    }
}
