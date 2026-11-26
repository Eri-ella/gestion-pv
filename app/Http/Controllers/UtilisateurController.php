<?php

namespace App\Http\Controllers;

use App\Models\Departement;
use App\Models\Role;
use App\Models\User;
use App\Support\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UtilisateurController extends Controller
{
    public function index()
    {
        return view('utilisateurs.index', [
            'users' => User::with(['role', 'departement'])->latest()->paginate(15),
            'roles' => Role::orderBy('libelle')->get(),
            'departements' => Departement::where('actif', true)->orderBy('libelle')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'departement_id' => ['nullable', 'integer', 'exists:departements,id'],
        ]);

        $role = Role::findOrFail($data['role_id']);
        if ($role->libelle === 'chef_departement' && empty($data['departement_id'])) {
            throw ValidationException::withMessages([
                'departement_id' => 'Le département est obligatoire pour un chef de département.',
            ]);
        }
        if ($role->libelle !== 'chef_departement') {
            $data['departement_id'] = null;
        }

        $data['password'] = Hash::make($data['password']);
        $data['actif'] = true;
        $user = User::create($data);

        Journal::enregistrer('user.created', $user->email, 'Compte utilisateur créé.');

        return back()->with('success', 'Utilisateur créé.');
    }

    public function toggle(Request $request, User $user)
    {
        abort_if($request->user()->is($user), 422, 'Vous ne pouvez pas désactiver votre propre compte.');
        $user->update(['actif' => ! $user->actif]);

        Journal::enregistrer(
            $user->actif ? 'user.enabled' : 'user.disabled',
            $user->email,
            'Statut du compte modifié.',
        );

        return back()->with('success', 'Statut utilisateur modifié.');
    }
}
