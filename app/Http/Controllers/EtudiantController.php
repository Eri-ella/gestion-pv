<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class EtudiantController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $etudiants = Etudiant::query()
            ->whereHas('resultats.pv', fn (Builder $query) => $query->visiblePar($user))
            ->with(['promotion.annee', 'promotion.filiere', 'promotion.niveau'])
            ->when($request->input('q'), fn ($q, $value) => $q->where(function ($q) use ($value) {
                $q->where('matricule', 'like', "%{$value}%")
                    ->orWhere('nom', 'like', "%{$value}%")
                    ->orWhere('prenoms', 'like', "%{$value}%");
            }))
            ->orderBy('nom')
            ->paginate(15)
            ->withQueryString();

        return view('etudiants.index', compact('etudiants'));
    }

    public function show(Request $request, Etudiant $etudiant)
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $etudiant = Etudiant::query()
            ->whereHas('resultats.pv', fn (Builder $query) => $query->visiblePar($user))
            ->with([
                'promotion.annee',
                'promotion.filiere',
                'promotion.niveau',
                'resultats.pv' => fn (Builder $query) => $query->visiblePar($user),
            ])
            ->findOrFail($etudiant->getKey());

        return view('etudiants.show', compact('etudiant'));
    }
}
