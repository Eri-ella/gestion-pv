<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\Filiere;
use App\Models\Pv;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $pvTotal = Pv::visiblePar($user)->count();
        $pvNumerises = Pv::visiblePar($user)->whereNotNull('fichier_path')->count();
        $pvValides = Pv::visiblePar($user)->where('statut', 'valide')->count();

        $tauxParFiliere = Filiere::query()
            ->whereHas('promotions.pv', fn (Builder $query) => $query->visiblePar($user))
            ->withCount([
                'promotions as pv_total' => fn (Builder $query) => $query->whereHas(
                    'pv',
                    fn (Builder $pvs) => $pvs->visiblePar($user),
                ),
                'promotions as pv_valides' => fn (Builder $query) => $query->whereHas(
                    'pv',
                    fn (Builder $pvs) => $pvs->visiblePar($user)->where('statut', 'valide'),
                ),
            ])
            ->orderBy('libelle')
            ->get()
            ->map(fn (Filiere $filiere) => [
                'filiere' => $filiere->libelle,
                'annee' => '',
                'taux' => $filiere->pv_total > 0
                    ? round(($filiere->pv_valides / $filiere->pv_total) * 100, 1)
                    : 0,
            ]);

        return view('dashboard', [
            'stats' => [
                'pv_numerises' => $pvNumerises,
                'pv_attendus' => max(0, $pvTotal - $pvNumerises),
                'pv_total' => $pvTotal,
                'etudiants' => Etudiant::whereHas(
                    'resultats.pv',
                    fn (Builder $query) => $query->visiblePar($user),
                )->distinct()->count('etudiants.id'),
                'taux_global' => $pvTotal > 0 ? round(($pvValides / $pvTotal) * 100, 1) : 0,
            ],
            'tauxParFiliere' => $tauxParFiliere,
            'pvManquants' => Pv::visiblePar($user)->doesntHave('resultats')
                ->with(['promotion.annee', 'promotion.filiere', 'promotion.niveau', 'membres', 'resultats.etudiant', 'validateur'])
                ->limit(10)->get()
                ->map(fn ($pv) => ['id' => $pv->id, 'libelle' => $pv->reference]),
        ]);
    }
}
