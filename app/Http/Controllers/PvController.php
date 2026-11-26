<?php

namespace App\Http\Controllers;

use App\Models\AnneeUniversitaire;
use App\Models\EvenementJournal;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Models\Promotion;
use App\Models\Pv;
use App\Models\Resultat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PvController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pvs = Pv::query()
            ->with(['promotion.annee', 'promotion.filiere', 'promotion.niveau'])
            ->when(request('annee_id'), fn ($q, $id) => $q->whereHas('promotion', fn ($q) => $q->where('annee_universitaire_id', $id)))
            ->when(request('filiere_id'), fn ($q, $id) => $q->whereHas('promotion', fn ($q) => $q->where('filiere_id', $id)))
            ->when(request('niveau_id'), fn ($q, $id) => $q->whereHas('promotion', fn ($q) => $q->where('niveau_id', $id)))
            ->when(request('statut'), fn ($q, $value) => $q->where('statut', $value))
            ->when(request('decision'), fn ($q, $value) => $q->whereHas('resultats', fn ($q) => $q->where('decision', $value)))
            ->when(request('q'), fn ($q, $value) => $q->where('reference', 'like', "%{$value}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pvs.index', [
            'pvs' => $pvs,
            'annees' => AnneeUniversitaire::orderBy('libelle')->get(),
            'filieres' => Filiere::orderBy('libelle')->get(),
            'niveaux' => Niveau::orderBy('ordre')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_unless(request()->user()->isRole('administrateur', 'agent_scolarite'), 403);

        return view('pvs.create', [
            'promotions' => Promotion::with(['annee', 'filiere', 'niveau'])->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless($request->user()->isRole('administrateur', 'agent_scolarite'), 403);
        $data = $request->validate([
            'promotion_id' => ['required', 'exists:promotions,id', 'unique:pvs,promotion_id'],
            'reference' => ['required', 'string', 'max:255', 'unique:pvs,reference'],
            'date_deliberation' => ['nullable', 'date'],
            'president_jury' => ['nullable', 'string', 'max:255'],
            'fichier' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        if ($request->hasFile('fichier')) {
            $data['fichier_path'] = $request->file('fichier')->store('pvs', 'private');
        }
        $data['cree_par'] = $request->user()->id;
        $data['statut'] = 'brouillon';
        unset($data['fichier']);
        $pv = Pv::create($data);
        $this->journal($request, 'pv.created', $pv->reference);

        return redirect()->route('pvs.show', $pv)->with('success', 'PV créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pv = Pv::with([
            'promotion.annee', 'promotion.filiere', 'promotion.niveau',
            'membres', 'resultats.etudiant', 'validateur',
        ])->findOrFail($id);

        return view('pvs.show', compact('pv'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        abort_unless(request()->user()->isRole('administrateur', 'agent_scolarite'), 403);
        $pv = Pv::with('promotion')->findOrFail($id);

        return view('pvs.edit', ['pv' => $pv, 'promotions' => Promotion::with(['annee', 'filiere', 'niveau'])->get()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        abort_unless($request->user()->isRole('administrateur', 'agent_scolarite'), 403);
        $pv = Pv::findOrFail($id);
        abort_unless($pv->statut === 'brouillon', 422);

        $data = $request->validate([
            'promotion_id' => ['required', 'exists:promotions,id', Rule::unique('pvs', 'promotion_id')->ignore($pv->id)],
            'reference' => ['required', 'string', 'max:255', Rule::unique('pvs', 'reference')->ignore($pv->id)],
            'date_deliberation' => ['nullable', 'date'],
            'president_jury' => ['nullable', 'string', 'max:255'],
            'fichier' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);
        if ($request->hasFile('fichier')) {
            if ($pv->fichier_path) {
                Storage::disk('private')->delete($pv->fichier_path);
            }
            $data['fichier_path'] = $request->file('fichier')->store('pvs', 'private');
        }
        unset($data['fichier']);
        $pv->update($data);
        $this->journal($request, 'pv.updated', $pv->reference);

        return redirect()->route('pvs.show', $pv)->with('success', 'PV modifié avec succès.');
    }

    public function valider(Request $request, Pv $pv)
    {
        abort_unless($request->user()->isRole('administrateur', 'chef_departement'), 403);
        $this->authorizeDepartment($request, $pv);
        abort_unless($pv->statut === 'brouillon', 422);

        $pv->update([
            'statut' => 'valide',
            'valide_par' => $request->user()->id,
            'date_validation' => now(),
        ]);
        $this->journal($request, 'pv.validated', $pv->reference);

        return back()->with('success', 'PV validé.');
    }

    public function archiver(Request $request, Pv $pv)
    {
        abort_unless($request->user()->isRole('administrateur', 'chef_departement'), 403);
        $this->authorizeDepartment($request, $pv);
        abort_unless($pv->statut === 'valide', 422);

        $pv->update(['statut' => 'archive']);
        $this->journal($request, 'pv.archived', $pv->reference);

        return back()->with('success', 'PV archivé.');
    }

    public function fichier(Pv $pv)
    {
        abort_unless($pv->fichier_path && Storage::disk('private')->exists($pv->fichier_path), 404);

        return Storage::disk('private')->download(
            $pv->fichier_path,
            basename($pv->fichier_path),
            ['Content-Disposition' => 'inline']
        );
    }

    public function importForm()
    {
        return view('pvs.import');
    }

    public function import(Request $request)
    {
        abort_unless($request->user()->isRole('administrateur', 'agent_scolarite'), 403);
        $data = $request->validate([
            'promotion_id' => ['required', 'exists:promotions,id'],
            'fichier' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:10240'],
        ]);

        $path = $request->file('fichier')->store('imports', 'private');
        $fullPath = Storage::disk('private')->path($path);
        $rows = array_map('str_getcsv', file($fullPath));
        $headers = array_shift($rows);

        $promotion = Promotion::findOrFail($data['promotion_id']);
        $pv = Pv::firstOrCreate(
            ['promotion_id' => $promotion->id],
            [
                'reference' => 'PV-'.$promotion->annee->libelle.'-'.$promotion->filiere->code.'-'.$promotion->niveau->libelle,
                'cree_par' => $request->user()->id,
                'statut' => 'brouillon',
            ]
        );

        $imported = 0;
        foreach ($rows as $row) {
            if (count($row) !== count($headers)) {
                continue;
            }
            $values = array_combine($headers, $row);
            if (! isset($values['matricule']) || ! isset($values['moyenne'])) {
                continue;
            }

            $etudiant = $promotion->etudiants()->where('matricule', trim($values['matricule']))->first();
            if (! $etudiant) {
                continue;
            }

            Resultat::updateOrCreate(
                ['pv_id' => $pv->id, 'etudiant_id' => $etudiant->id],
                [
                    'moyenne' => is_numeric($values['moyenne']) ? $values['moyenne'] : null,
                    'credits_valides' => isset($values['credits_valides']) && is_numeric($values['credits_valides']) ? $values['credits_valides'] : 0,
                    'decision' => in_array($values['decision'] ?? '', ['admis', 'ajourne', 'rattrapage'], true) ? $values['decision'] : null,
                ]
            );
            $imported++;
        }

        Storage::disk('private')->delete($path);
        $this->journal($request, 'pv.results.imported', $pv->reference.' ('.$imported.' lignes)');

        return redirect()->route('pvs.show', $pv)->with('success', $imported.' résultat(s) importé(s).');
    }

    public function storeResultats(Request $request, Pv $pv)
    {
        abort_unless($request->user()->isRole('administrateur', 'agent_scolarite'), 403);
        abort_unless($pv->statut === 'brouillon', 422);

        $data = $request->validate([
            'resultats' => ['required', 'array'],
            'resultats.*.etudiant_id' => ['required', 'integer', 'exists:etudiants,id'],
            'resultats.*.moyenne' => ['nullable', 'numeric', 'between:0,20'],
            'resultats.*.credits_valides' => ['nullable', 'numeric', 'min:0'],
            'resultats.*.decision' => ['nullable', Rule::in(['admis', 'ajourne', 'rattrapage'])],
        ]);

        foreach ($data['resultats'] as $resultat) {
            $etudiant = $pv->promotion->etudiants()->find($resultat['etudiant_id']);
            abort_unless($etudiant, 422);
            Resultat::updateOrCreate(
                ['pv_id' => $pv->id, 'etudiant_id' => $etudiant->id],
                [
                    'moyenne' => $resultat['moyenne'] ?? null,
                    'credits_valides' => $resultat['credits_valides'] ?? 0,
                    'decision' => $resultat['decision'] ?? null,
                ]
            );
        }

        $this->journal($request, 'pv.results.saved', $pv->reference);

        return back()->with('success', 'Résultats enregistrés.');
    }

    private function authorizeDepartment(Request $request, Pv $pv): void
    {
        $user = $request->user();
        if ($user->isRole('chef_departement') &&
            $pv->promotion->filiere->departement_id !== $user->departement_id) {
            abort(403);
        }
    }

    private function journal(Request $request, string $action, ?string $cible): void
    {
        EvenementJournal::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'cible' => $cible,
            'details' => $request->method().' '.$request->path(),
            'ip' => $request->ip(),
        ]);
    }
}
