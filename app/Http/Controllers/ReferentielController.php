<?php

namespace App\Http\Controllers;

use App\Models\AnneeUniversitaire;
use App\Models\Departement;
use App\Models\Filiere;
use App\Models\Niveau;
use Illuminate\Http\Request;

class ReferentielController extends Controller
{
    public function index()
    {
        return view('referentiels.index', [
            'annees' => AnneeUniversitaire::orderByDesc('libelle')->get(),
            'filieres' => Filiere::with('departement')->orderBy('libelle')->get(),
            'niveaux' => Niveau::orderBy('ordre')->get(),
            'departements' => Departement::orderBy('libelle')->get(),
        ]);
    }

    public function store(Request $request, string $type)
    {
        $data = $this->validateType($request, $type);

        match ($type) {
            'annees' => AnneeUniversitaire::create($data),
            'departements' => Departement::create($data),
            'filieres' => Filiere::create($data),
            'niveaux' => Niveau::create($data),
            default => abort(404),
        };

        return back()->with('success', 'Référentiel ajouté.');
    }

    public function destroy(string $type, int $id)
    {
        $model = match ($type) {
            'annees' => AnneeUniversitaire::findOrFail($id),
            'departements' => Departement::findOrFail($id),
            'filieres' => Filiere::findOrFail($id),
            'niveaux' => Niveau::findOrFail($id),
            default => abort(404),
        };

        try {
            $model->delete();
        } catch (\Throwable) {
            abort(422, 'Cet élément est utilisé et ne peut pas être supprimé.');
        }

        return back()->with('success', 'Référentiel supprimé.');
    }

    private function validateType(Request $request, string $type): array
    {
        return match ($type) {
            'annees' => $request->validate([
                'libelle' => ['required', 'string', 'max:255', 'unique:annee_universitaires,libelle'],
                'courante' => ['sometimes', 'boolean'],
            ]),
            'departements' => $request->validate([
                'libelle' => ['required', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:50', 'unique:departements,code'],
            ]),
            'filieres' => $request->validate([
                'departement_id' => ['required', 'integer', 'exists:departements,id'],
                'libelle' => ['required', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:50', 'unique:filieres,code'],
            ]),
            'niveaux' => $request->validate([
                'libelle' => ['required', 'string', 'max:255'],
                'ordre' => ['required', 'integer', 'min:1'],
            ]),
            default => abort(404),
        };
    }
}
