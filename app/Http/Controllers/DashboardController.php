<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\Promotion;
use App\Models\Pv;
use App\Models\Resultat;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $pvs = fn () => Pv::visiblePar($user);
        $promotions = fn () => Promotion::query()->when(
            ! $user->isRole('administrateur', 'agent_scolarite'),
            fn (Builder $query) => $query->whereHas(
                'filiere',
                fn (Builder $filieres) => $filieres->where('departement_id', $user->departement_id),
            ),
        );

        // Taux de réussite (admis / résultats avec décision) par filière et par année
        $lignes = Resultat::query()
            ->join('pvs', 'pvs.id', '=', 'resultats.pv_id')
            ->join('promotions', 'promotions.id', '=', 'pvs.promotion_id')
            ->join('filieres', 'filieres.id', '=', 'promotions.filiere_id')
            ->join('annee_universitaires', 'annee_universitaires.id', '=', 'promotions.annee_universitaire_id')
            ->whereIn('resultats.pv_id', $pvs()->select('pvs.id'))
            ->whereNotNull('resultats.decision')
            ->groupBy('filieres.code', 'annee_universitaires.libelle')
            ->orderBy('annee_universitaires.libelle')
            ->orderBy('filieres.code')
            ->selectRaw("filieres.code as filiere, annee_universitaires.libelle as annee, count(*) as total, sum(case when resultats.decision = 'admis' then 1 else 0 end) as admis")
            ->get();

        $filieres = $lignes->pluck('filiere')->unique()->values();
        $datasets = $lignes->pluck('annee')->unique()->values()->map(fn ($annee) => [
            'label' => $annee,
            'data' => $filieres->map(function ($filiere) use ($lignes, $annee) {
                $ligne = $lignes->first(fn ($l) => $l->annee === $annee && $l->filiere === $filiere);

                return $ligne ? round($ligne->admis / $ligne->total * 100, 1) : null;
            })->all(),
        ])->all();

        $totalResultats = $lignes->sum('total');
        $numerises = $pvs()->whereNotNull('fichier_path')->count();
        $attendus = $promotions()->count();

        return view('dashboard', [
            'stats' => [
                'pv_numerises' => $numerises,
                'pv_attendus' => $attendus,
                'pv_valides' => $pvs()->whereIn('statut', ['valide', 'archive'])->count(),
                'etudiants' => Etudiant::whereHas(
                    'resultats.pv',
                    fn (Builder $query) => $query->visiblePar($user),
                )->count(),
                'taux_global' => $totalResultats > 0 ? round($lignes->sum('admis') / $totalResultats * 100, 1) : 0,
            ],
            'chartTaux' => ['labels' => $filieres->all(), 'datasets' => $datasets],
            'derniersPv' => $pvs()->with(['promotion.filiere', 'promotion.niveau', 'promotion.annee'])
                ->latest()->limit(6)->get(),
            'pvManquants' => $promotions()->doesntHave('pv')
                ->with(['annee', 'filiere', 'niveau'])->limit(8)->get(),
        ]);
    }
}
