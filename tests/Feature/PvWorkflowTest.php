<?php

use App\Models\AnneeUniversitaire;
use App\Models\Departement;
use App\Models\Etudiant;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Models\Promotion;
use App\Models\Pv;
use App\Models\Resultat;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function createPvContext(string $suffix, string $statut = 'brouillon'): array
{
    $roles = collect(['administrateur', 'agent_scolarite', 'chef_departement'])
        ->mapWithKeys(fn (string $libelle) => [$libelle => Role::firstOrCreate(['libelle' => $libelle])]);
    $departement = Departement::create(['libelle' => 'Département '.$suffix, 'code' => 'D-'.$suffix]);
    $filiere = Filiere::create([
        'departement_id' => $departement->id,
        'libelle' => 'Filière '.$suffix,
        'code' => 'F-'.$suffix,
    ]);
    $annee = AnneeUniversitaire::create(['libelle' => 'Année '.$suffix, 'courante' => true]);
    $niveau = Niveau::create(['libelle' => 'Niveau '.$suffix, 'ordre' => 1]);
    $promotion = Promotion::create([
        'annee_universitaire_id' => $annee->id,
        'filiere_id' => $filiere->id,
        'niveau_id' => $niveau->id,
    ]);
    $admin = User::factory()->create([
        'role_id' => $roles['administrateur']->id,
        'departement_id' => null,
    ]);
    $agent = User::factory()->create([
        'role_id' => $roles['agent_scolarite']->id,
        'departement_id' => null,
    ]);
    $chef = User::factory()->create([
        'role_id' => $roles['chef_departement']->id,
        'departement_id' => $departement->id,
    ]);
    $chefSansDepartement = User::factory()->create([
        'role_id' => $roles['chef_departement']->id,
        'departement_id' => null,
    ]);
    $pv = Pv::create([
        'promotion_id' => $promotion->id,
        'reference' => 'PV-'.$suffix,
        'statut' => $statut,
        'cree_par' => $agent->id,
        'valide_par' => $statut === 'brouillon' ? null : $admin->id,
        'date_validation' => $statut === 'brouillon' ? null : now(),
    ]);
    $etudiant = Etudiant::create([
        'promotion_id' => $promotion->id,
        'matricule' => 'M-'.$suffix,
        'nom' => 'Nom',
        'prenoms' => 'Prénom '.$suffix,
    ]);
    Resultat::create([
        'pv_id' => $pv->id,
        'etudiant_id' => $etudiant->id,
        'moyenne' => 12,
        'credits_valides' => 60,
        'decision' => 'admis',
    ]);

    return compact(
        'admin',
        'agent',
        'annee',
        'chef',
        'chefSansDepartement',
        'departement',
        'etudiant',
        'filiere',
        'niveau',
        'promotion',
        'pv',
    );
}

function updatePvPayload(array $context): array
{
    return [
        'promotion_id' => $context['promotion']->id,
        'reference' => $context['pv']->reference,
        'date_deliberation' => '2026-06-15',
        'president_jury' => 'Président',
    ];
}

it('allows admin and agent draft updates and refuses the chef role', function () {
    foreach (['admin', 'agent'] as $role) {
        $context = createPvContext($role);

        $this->actingAs($context[$role])
            ->put(route('pvs.update', $context['pv']), updatePvPayload($context))
            ->assertRedirect(route('pvs.show', $context['pv']));
    }

    $context = createPvContext('chef-update');
    $this->actingAs($context['chef'])
        ->put(route('pvs.update', $context['pv']), updatePvPayload($context))
        ->assertForbidden();
});

it('allows only administrators and department chiefs to validate and archive', function () {
    $context = createPvContext('chef-transition');

    $this->actingAs($context['chef'])
        ->post(route('pvs.valider', $context['pv']))
        ->assertRedirect();
    $this->assertDatabaseHas('pvs', ['id' => $context['pv']->id, 'statut' => 'valide']);

    $this->post(route('pvs.archiver', $context['pv']))
        ->assertRedirect();
    $this->assertDatabaseHas('pvs', ['id' => $context['pv']->id, 'statut' => 'archive']);

    $agentContext = createPvContext('agent-transition');
    $this->actingAs($agentContext['agent'])
        ->post(route('pvs.valider', $agentContext['pv']))
        ->assertForbidden();

    $adminContext = createPvContext('admin-transition');
    $this->actingAs($adminContext['admin'])
        ->post(route('pvs.valider', $adminContext['pv']))
        ->assertRedirect();
});

it('rejects invalid status transitions', function () {
    $draft = createPvContext('draft-archive');
    $this->actingAs($draft['chef'])
        ->post(route('pvs.archiver', $draft['pv']))
        ->assertUnprocessable();

    $archived = createPvContext('archive-validate', 'archive');
    $this->actingAs($archived['admin'])
        ->post(route('pvs.valider', $archived['pv']))
        ->assertUnprocessable();
});

it('locks validated PV writes except for logged administrator overrides', function () {
    $context = createPvContext('locked', 'valide');
    $resultats = [
        'resultats' => [[
            'etudiant_id' => $context['etudiant']->id,
            'moyenne' => 14,
            'credits_valides' => 60,
            'decision' => 'admis',
        ]],
    ];

    $this->actingAs($context['agent'])
        ->put(route('pvs.update', $context['pv']), updatePvPayload($context))
        ->assertUnprocessable();
    $this->post(route('pvs.resultats.store', $context['pv']), $resultats)
        ->assertUnprocessable();

    $this->post(route('pvs.import.store'), [
        'promotion_id' => $context['promotion']->id,
        'fichier' => UploadedFile::fake()->createWithContent(
            'locked.csv',
            "matricule,nom,prenoms,moyenne\nM-locked,Nom,Prénom,13\n",
        ),
    ])->assertUnprocessable();

    $this->actingAs($context['admin'])
        ->put(route('pvs.update', $context['pv']), updatePvPayload($context))
        ->assertRedirect(route('pvs.show', $context['pv']));
    $this->post(route('pvs.resultats.store', $context['pv']), $resultats)
        ->assertRedirect();

    $adminCsv = UploadedFile::fake()->createWithContent(
        'admin-locked.csv',
        "matricule,nom,prenoms,moyenne\nM-locked,Nom,Prénom,13\n",
    );
    $this->post(route('pvs.import.store'), [
        'promotion_id' => $context['promotion']->id,
        'fichier' => $adminCsv,
    ])->assertRedirect(route('pvs.show', $context['pv']));

    $this->assertDatabaseHas('evenement_journals', [
        'action' => 'pv.locked_update_override',
        'cible' => $context['pv']->reference,
    ]);
    $this->assertDatabaseHas('evenement_journals', [
        'action' => 'pv.locked_resultats_override',
        'cible' => $context['pv']->reference,
    ]);
    $this->assertDatabaseHas('evenement_journals', [
        'action' => 'pv.locked_import_override',
        'cible' => $context['pv']->reference,
    ]);
});

it('limits PV, student, and detail access to visible departments', function () {
    $own = createPvContext('scope-own');
    $other = createPvContext('scope-other');

    expect(Pv::visiblePar($own['chef'])->pluck('id')->all())->toBe([$own['pv']->id]);
    expect(Etudiant::whereHas(
        'resultats.pv',
        fn ($query) => $query->visiblePar($own['chef']),
    )->pluck('id')->all())->toBe([$own['etudiant']->id]);

    $this->actingAs($own['chef']);
    $this->get(route('pvs.show', $other['pv']))->assertNotFound();
    $this->get(route('etudiants.show', $other['etudiant']))->assertNotFound();

    $this->actingAs($own['chefSansDepartement']);
    expect(Pv::visiblePar($own['chefSansDepartement'])->count())->toBe(0);
    $this->get(route('pvs.show', $own['pv']))->assertNotFound();
});

it('serves private PV files only to users who can see that PV and logs access', function () {
    Storage::fake('local');
    Storage::disk('local')->put('pvs/private.pdf', 'private pdf content');
    $context = createPvContext('private-file');
    $context['pv']->update(['fichier_path' => 'pvs/private.pdf']);

    $this->actingAs($context['chef'])
        ->get(route('pvs.fichier', $context['pv']))
        ->assertOk()
        ->assertHeader('content-disposition', 'inline; filename=private.pdf');

    $this->assertDatabaseHas('evenement_journals', [
        'action' => 'pv.scan_consulte',
        'cible' => $context['pv']->reference,
    ]);

    $other = createPvContext('private-hidden');
    $this->get(route('pvs.fichier', $other['pv']))->assertNotFound();
    Storage::disk('local')->delete('pvs/private.pdf');
});

it('imports CSV rows transactionally and reports malformed rows by line', function () {
    $context = createPvContext('csv-import');
    $csv = UploadedFile::fake()->createWithContent(
        'resultats.csv',
        "matricule,nom,prenoms,moyenne,credits_valides,decision\n".
        "M-csv-import,Nom,Prénom,15,60,admis\n".
        "NEW-001,Nouveau,Étudiant,12,45,admis\n".
        "BAD-001,Nom,Prénom,25,60,admis\n",
    );

    $this->actingAs($context['agent'])
        ->post(route('pvs.import.store'), [
            'promotion_id' => $context['promotion']->id,
            'fichier' => $csv,
        ])
        ->assertRedirect(route('pvs.show', $context['pv']))
        ->assertSessionHas('import_errors', [
            ['ligne' => 4, 'message' => 'Moyenne invalide.'],
        ]);

    $this->assertDatabaseHas('resultats', [
        'pv_id' => $context['pv']->id,
        'etudiant_id' => $context['etudiant']->id,
        'moyenne' => 15,
    ]);
    $this->assertDatabaseHas('etudiants', ['matricule' => 'NEW-001']);
    $this->assertDatabaseHas('resultats', [
        'pv_id' => $context['pv']->id,
        'etudiant_id' => Etudiant::where('matricule', 'NEW-001')->value('id'),
        'moyenne' => 12,
    ]);
});
