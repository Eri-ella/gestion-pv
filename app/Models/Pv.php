<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Pv extends Model
{
    protected $fillable = [
        'promotion_id', 'reference', 'statut', 'date_deliberation',
        'president_jury', 'fichier_path', 'cree_par', 'valide_par', 'date_validation',
    ];

    protected function casts(): array
    {
        return [
            'date_deliberation' => 'date',
            'date_validation' => 'datetime',
        ];
    }

    public function scopeVisiblePar(Builder $query, User $user): Builder
    {
        if ($user->isRole('administrateur', 'agent_scolarite')) {
            return $query;
        }

        if (! $user->isRole('chef_departement') || ! $user->departement_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas(
            'promotion.filiere',
            fn (Builder $filieres) => $filieres->where('departement_id', $user->departement_id),
        );
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function annee()
    {
        return $this->hasOneThrough(
            AnneeUniversitaire::class,
            Promotion::class,
            'id',
            'id',
            'promotion_id',
            'annee_universitaire_id'
        );
    }

    public function filiere()
    {
        return $this->hasOneThrough(Filiere::class, Promotion::class, 'id', 'id', 'promotion_id', 'filiere_id');
    }

    public function niveau()
    {
        return $this->hasOneThrough(Niveau::class, Promotion::class, 'id', 'id', 'promotion_id', 'niveau_id');
    }

    public function membres()
    {
        return $this->hasMany(PvMembre::class);
    }

    public function resultats()
    {
        return $this->hasMany(Resultat::class);
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }
}
