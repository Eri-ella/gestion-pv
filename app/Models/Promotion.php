<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = ['annee_universitaire_id', 'filiere_id', 'niveau_id'];

    protected function libelle(): Attribute
    {
        return Attribute::get(fn () => implode(' · ', array_filter([
            $this->filiere?->code,
            $this->niveau?->libelle,
            $this->annee?->libelle,
        ])));
    }

    public function annee()
    {
        return $this->belongsTo(AnneeUniversitaire::class, 'annee_universitaire_id');
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }

    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function etudiants()
    {
        return $this->hasMany(Etudiant::class);
    }

    public function pv()
    {
        return $this->hasOne(Pv::class);
    }
}
