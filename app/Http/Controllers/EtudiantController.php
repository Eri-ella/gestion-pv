<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;

class EtudiantController extends Controller
{
    public function index()
    {
        $etudiants = Etudiant::query()
            ->with(['promotion.annee', 'promotion.filiere', 'promotion.niveau'])
            ->when(request('q'), fn ($q, $value) => $q->where(function ($q) use ($value) {
                $q->where('matricule', 'like', "%{$value}%")
                    ->orWhere('nom', 'like', "%{$value}%")
                    ->orWhere('prenoms', 'like', "%{$value}%");
            }))
            ->orderBy('nom')
            ->paginate(15)
            ->withQueryString();

        return view('etudiants.index', compact('etudiants'));
    }

    public function show(Etudiant $etudiant)
    {
        $etudiant->load(['promotion.annee', 'promotion.filiere', 'promotion.niveau', 'resultats.pv']);

        return view('etudiants.show', compact('etudiant'));
    }
}
