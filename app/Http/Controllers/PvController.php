<?php

namespace App\Http\Controllers;

use App\Models\AnneeUniversitaire;
use App\Models\Etudiant;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Models\Promotion;
use App\Models\Pv;
use App\Models\Resultat;
use App\Models\User;
use App\Support\Journal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PvController extends Controller
{
    private const PV_RELATIONS = [
        'promotion.annee',
        'promotion.filiere',
        'promotion.niveau',
        'membres',
        'resultats.etudiant',
        'validateur',
    ];

    public function index(Request $request)
    {
        $user = $this->authenticatedUser($request);
        $pvs = Pv::visiblePar($user)
            ->with(self::PV_RELATIONS)
            ->when($request->input('annee_id'), fn (Builder $query, $id) => $query->whereHas(
                'promotion',
                fn (Builder $promotions) => $promotions->where('annee_universitaire_id', $id),
            ))
            ->when($request->input('filiere_id'), fn (Builder $query, $id) => $query->whereHas(
                'promotion',
                fn (Builder $promotions) => $promotions->where('filiere_id', $id),
            ))
            ->when($request->input('niveau_id'), fn (Builder $query, $id) => $query->whereHas(
                'promotion',
                fn (Builder $promotions) => $promotions->where('niveau_id', $id),
            ))
            ->when($request->input('statut'), fn (Builder $query, $value) => $query->where('statut', $value))
            ->when($request->input('decision'), fn (Builder $query, $value) => $query->whereHas(
                'resultats',
                fn (Builder $resultats) => $resultats->where('decision', $value),
            ))
            ->when($request->input('q'), fn (Builder $query, $value) => $query->where(
                'reference',
                'like',
                "%{$value}%",
            ))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pvs.index', [
            'pvs' => $pvs,
            'annees' => AnneeUniversitaire::whereHas(
                'promotions.pv',
                fn (Builder $query) => $query->visiblePar($user),
            )->orderBy('libelle')->get(),
            'filieres' => Filiere::whereHas(
                'promotions.pv',
                fn (Builder $query) => $query->visiblePar($user),
            )->orderBy('libelle')->get(),
            'niveaux' => Niveau::whereHas(
                'promotions.pv',
                fn (Builder $query) => $query->visiblePar($user),
            )->orderBy('ordre')->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizeRole($this->authenticatedUser($request), 'administrateur', 'agent_scolarite');

        return view('pvs.create', [
            'promotions' => Promotion::with(['annee', 'filiere', 'niveau'])->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->authenticatedUser($request);
        $this->authorizeRole($user, 'administrateur', 'agent_scolarite');

        $data = $request->validate([
            'promotion_id' => ['required', 'exists:promotions,id', 'unique:pvs,promotion_id'],
            'reference' => ['required', 'string', 'max:255', 'unique:pvs,reference'],
            'date_deliberation' => ['nullable', 'date'],
            'president_jury' => ['nullable', 'string', 'max:255'],
            'fichier' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'membres' => ['nullable', 'array'],
            'membres.*.nom' => ['required_with:membres', 'string', 'max:255'],
            'membres.*.fonction' => ['nullable', 'string', 'max:255'],
        ]);

        $membres = $data['membres'] ?? [];
        unset($data['fichier'], $data['membres']);
        $data['cree_par'] = $user->id;
        $data['statut'] = 'brouillon';

        $pv = DB::transaction(function () use ($data, $membres) {
            $pv = Pv::create($data);
            $this->syncMembres($pv, $membres);

            return $pv;
        });
        Journal::enregistrer('pv.created', $pv->reference, $request->method().' '.$request->path());

        return redirect()->route('pvs.show', $pv)->with('success', 'PV créé avec succès.');
    }

    public function show(Request $request, string $id)
    {
        $pv = $this->visiblePv($this->authenticatedUser($request), $id);

        return view('pvs.show', compact('pv'));
    }

    public function edit(Request $request, string $id)
    {
        $user = $this->authenticatedUser($request);
        $this->authorizeRole($user, 'administrateur', 'agent_scolarite');
        $pv = $this->visiblePv($user, $id);
        $this->ensureEditable($pv, $user);

        return view('pvs.edit', [
            'pv' => $pv,
            'promotions' => Promotion::with(['annee', 'filiere', 'niveau'])->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $user = $this->authenticatedUser($request);
        $this->authorizeRole($user, 'administrateur', 'agent_scolarite');
        $pv = $this->visiblePv($user, $id);
        $lockedOverride = $this->ensureEditable($pv, $user);

        $data = $request->validate([
            'promotion_id' => [
                'required',
                'exists:promotions,id',
                Rule::unique('pvs', 'promotion_id')->ignore($pv->id),
            ],
            'reference' => ['required', 'string', 'max:255', Rule::unique('pvs', 'reference')->ignore($pv->id)],
            'date_deliberation' => ['nullable', 'date'],
            'president_jury' => ['nullable', 'string', 'max:255'],
            'fichier' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'membres' => ['nullable', 'array'],
            'membres.*.nom' => ['required_with:membres', 'string', 'max:255'],
            'membres.*.fonction' => ['nullable', 'string', 'max:255'],
        ]);

        abort_if(
            (int) $data['promotion_id'] !== (int) $pv->promotion_id && $pv->resultats()->exists(),
            422,
            'La promotion ne peut pas changer après la saisie des résultats.',
        );

        $oldFilePath = $pv->fichier_path;
        $newFilePath = $this->storePrivateFile($request);
        if ($newFilePath !== null) {
            $data['fichier_path'] = $newFilePath;
        }

        $membres = $data['membres'] ?? [];
        unset($data['fichier'], $data['membres']);

        DB::transaction(function () use ($pv, $data, $membres) {
            $pv->update($data);
            $this->syncMembres($pv, $membres);
        });

        if ($newFilePath !== null && $oldFilePath !== null) {
            Storage::disk('local')->delete($oldFilePath);
        }

        if ($lockedOverride) {
            $this->logLockedOverride($pv, 'update');
        }
        Journal::enregistrer('pv.updated', $pv->reference, $request->method().' '.$request->path());

        return redirect()->route('pvs.show', $pv)->with('success', 'PV modifié avec succès.');
    }

    public function valider(Request $request, Pv $pv)
    {
        $user = $this->authenticatedUser($request);
        $this->authorizeRole($user, 'administrateur', 'chef_departement');
        $pv = $this->visiblePv($user, (string) $pv->getKey());
        abort_unless($pv->statut === 'brouillon', 422, 'Seul un PV brouillon peut être validé.');

        $pv->update([
            'statut' => 'valide',
            'valide_par' => $user->id,
            'date_validation' => now(),
        ]);
        Journal::enregistrer('pv.validated', $pv->reference, 'PV validé.');

        return back()->with('success', 'PV validé.');
    }

    public function archiver(Request $request, Pv $pv)
    {
        $user = $this->authenticatedUser($request);
        $this->authorizeRole($user, 'administrateur', 'chef_departement');
        $pv = $this->visiblePv($user, (string) $pv->getKey());
        abort_unless($pv->statut === 'valide', 422, 'Seul un PV validé peut être archivé.');

        $pv->update(['statut' => 'archive']);
        Journal::enregistrer('pv.archived', $pv->reference, 'PV archivé.');

        return back()->with('success', 'PV archivé.');
    }

    public function fichier(Request $request, Pv $pv)
    {
        $pv = $this->visiblePv($this->authenticatedUser($request), (string) $pv->getKey());
        abort_unless(
            $pv->fichier_path && Storage::disk('local')->exists($pv->fichier_path),
            404,
            'Le fichier de ce PV est introuvable.',
        );

        Journal::enregistrer('pv.scan_consulte', $pv->reference, 'Fichier du PV consulté.');

        return Storage::disk('local')->response(
            $pv->fichier_path,
            basename($pv->fichier_path),
            [],
            'inline',
        );
    }

    public function importForm(Request $request)
    {
        $user = $this->authenticatedUser($request);
        $this->authorizeRole($user, 'administrateur', 'agent_scolarite');

        return view('pvs.import', [
            'promotions' => Promotion::with(['annee', 'filiere', 'niveau'])->get(),
        ]);
    }

    public function import(Request $request)
    {
        $user = $this->authenticatedUser($request);
        $this->authorizeRole($user, 'administrateur', 'agent_scolarite');
        $data = $request->validate([
            'promotion_id' => ['required', 'exists:promotions,id'],
            'fichier' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $handle = fopen($request->file('fichier')->getRealPath(), 'rb');
        if ($handle === false) {
            throw new RuntimeException('Impossible de lire le fichier CSV importé.');
        }

        try {
            $headers = fgetcsv($handle);
            if ($headers === false) {
                throw ValidationException::withMessages(['fichier' => 'Le fichier CSV est vide.']);
            }

            $headers = array_map(
                fn ($header) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $header))),
                $headers,
            );
            if (! in_array('matricule', $headers, true) || ! in_array('moyenne', $headers, true)) {
                throw ValidationException::withMessages([
                    'fichier' => 'Le CSV doit contenir les colonnes matricule et moyenne.',
                ]);
            }

            $promotion = Promotion::with(['annee', 'filiere', 'niveau'])->findOrFail($data['promotion_id']);
            [$pv, $imported, $errors] = DB::transaction(function () use ($headers, $handle, $promotion, $user) {
                $pv = Pv::firstOrCreate(
                    ['promotion_id' => $promotion->id],
                    [
                        'reference' => sprintf(
                            'PV-%s-%s-%s',
                            $promotion->annee->libelle,
                            $promotion->filiere->code,
                            $promotion->niveau->ordre,
                        ),
                        'cree_par' => $user->id,
                        'statut' => 'brouillon',
                    ],
                );
                $pv = $this->visiblePv($user, (string) $pv->getKey());
                $lockedOverride = $this->ensureEditable($pv, $user);

                $imported = 0;
                $errors = [];
                $lineNumber = 1;

                while (($row = fgetcsv($handle)) !== false) {
                    $lineNumber++;
                    if ($row === [null] || trim(implode('', $row)) === '') {
                        continue;
                    }

                    if (count($row) !== count($headers)) {
                        $errors[] = ['ligne' => $lineNumber, 'message' => 'Nombre de colonnes invalide.'];

                        continue;
                    }

                    $values = array_combine($headers, $row);
                    $matricule = trim((string) ($values['matricule'] ?? ''));
                    $moyenne = trim((string) ($values['moyenne'] ?? ''));
                    $credits = trim((string) ($values['credits_valides'] ?? ''));
                    $decision = trim((string) ($values['decision'] ?? ''));

                    $rowErrors = [];
                    if ($matricule === '') {
                        $rowErrors[] = 'Matricule manquant.';
                    }
                    if ($moyenne !== '' && (! is_numeric($moyenne) || $moyenne < 0 || $moyenne > 20)) {
                        $rowErrors[] = 'Moyenne invalide.';
                    }
                    if ($credits !== '' && (! is_numeric($credits) || $credits < 0)) {
                        $rowErrors[] = 'Crédits validés invalides.';
                    }
                    if ($decision !== '' && ! in_array($decision, ['admis', 'ajourne', 'rattrapage'], true)) {
                        $rowErrors[] = 'Décision invalide.';
                    }

                    $existingStudent = $matricule === ''
                        ? null
                        : Etudiant::where('matricule', $matricule)->first();
                    if (
                        $existingStudent === null
                        && (trim((string) ($values['nom'] ?? '')) === '' || trim((string) ($values['prenoms'] ?? '')) === '')
                    ) {
                        $rowErrors[] = 'Nom et prénoms sont requis pour créer un étudiant.';
                    }

                    if ($rowErrors !== []) {
                        $errors[] = ['ligne' => $lineNumber, 'message' => implode(' ', $rowErrors)];

                        continue;
                    }

                    $etudiant = Etudiant::firstOrCreate(
                        ['matricule' => $matricule],
                        [
                            'promotion_id' => $promotion->id,
                            'nom' => trim((string) ($values['nom'] ?? '')),
                            'prenoms' => trim((string) ($values['prenoms'] ?? '')),
                        ],
                    );

                    if ((int) $etudiant->promotion_id !== (int) $promotion->id) {
                        $errors[] = [
                            'ligne' => $lineNumber,
                            'message' => 'Cet étudiant est déjà rattaché à une autre promotion.',
                        ];

                        continue;
                    }

                    Resultat::updateOrCreate(
                        ['pv_id' => $pv->id, 'etudiant_id' => $etudiant->id],
                        [
                            'moyenne' => $moyenne === '' ? null : $moyenne,
                            'credits_valides' => $credits === '' ? 0 : $credits,
                            'decision' => $decision === '' ? null : $decision,
                        ],
                    );
                    $imported++;
                }

                Journal::enregistrer(
                    'pv.results.imported',
                    $pv->reference,
                    sprintf('%d ligne(s) importée(s), %d erreur(s).', $imported, count($errors)),
                );
                if ($lockedOverride) {
                    $this->logLockedOverride($pv, 'import');
                }

                return [$pv, $imported, $errors];
            });
        } finally {
            fclose($handle);
        }

        return redirect()->route('pvs.show', $pv)
            ->with('success', $imported.' résultat(s) importé(s).')
            ->with('import_errors', $errors);
    }

    public function storeResultats(Request $request, Pv $pv)
    {
        $user = $this->authenticatedUser($request);
        $this->authorizeRole($user, 'administrateur', 'agent_scolarite');
        $pv = $this->visiblePv($user, (string) $pv->getKey());
        $lockedOverride = $this->ensureEditable($pv, $user);

        $data = $request->validate([
            'resultats' => ['required', 'array'],
            'resultats.*.etudiant_id' => ['required', 'integer', 'exists:etudiants,id'],
            'resultats.*.moyenne' => ['nullable', 'numeric', 'between:0,20'],
            'resultats.*.credits_valides' => ['nullable', 'numeric', 'min:0'],
            'resultats.*.decision' => ['nullable', Rule::in(['admis', 'ajourne', 'rattrapage'])],
        ]);

        DB::transaction(function () use ($data, $pv): void {
            foreach ($data['resultats'] as $resultat) {
                $etudiant = $pv->promotion->etudiants()->find($resultat['etudiant_id']);
                abort_unless($etudiant, 422, 'L’étudiant ne fait pas partie de la promotion du PV.');

                Resultat::updateOrCreate(
                    ['pv_id' => $pv->id, 'etudiant_id' => $etudiant->id],
                    [
                        'moyenne' => $resultat['moyenne'] ?? null,
                        'credits_valides' => $resultat['credits_valides'] ?? 0,
                        'decision' => $resultat['decision'] ?? null,
                    ],
                );
            }
        });

        if ($lockedOverride) {
            $this->logLockedOverride($pv, 'resultats');
        }
        Journal::enregistrer('pv.results.saved', $pv->reference, 'Résultats enregistrés.');

        return back()->with('success', 'Résultats enregistrés.');
    }

    private function authenticatedUser(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function authorizeRole(User $user, string ...$roles): void
    {
        abort_unless($user->isRole(...$roles), 403);
    }

    private function visiblePv(User $user, string $id): Pv
    {
        return Pv::visiblePar($user)->with(self::PV_RELATIONS)->findOrFail($id);
    }

    private function ensureEditable(Pv $pv, User $user): bool
    {
        if (! in_array($pv->statut, ['valide', 'archive'], true)) {
            return false;
        }

        abort_unless($user->isRole('administrateur'), 422, 'Ce PV est verrouillé.');

        return true;
    }

    private function logLockedOverride(Pv $pv, string $operation): void
    {
        Journal::enregistrer(
            'pv.locked_'.$operation.'_override',
            $pv->reference,
            'Modification administrative d’un PV '.$pv->statut.'.',
        );
    }

    private function storePrivateFile(Request $request): ?string
    {
        if (! $request->hasFile('fichier')) {
            return null;
        }

        $path = $request->file('fichier')->store('pvs', 'local');
        abort_unless(is_string($path), 500, 'Le fichier du PV n’a pas pu être enregistré.');

        return $path;
    }

    private function syncMembres(Pv $pv, array $membres): void
    {
        $pv->membres()->delete();

        foreach ($membres as $membre) {
            if (trim((string) ($membre['nom'] ?? '')) === '') {
                continue;
            }

            $pv->membres()->create([
                'nom' => trim($membre['nom']),
                'fonction' => trim((string) ($membre['fonction'] ?? '')) ?: null,
                'president' => false,
            ]);
        }
    }
}
