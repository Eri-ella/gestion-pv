<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    protected $fillable = ['promotion_id', 'matricule', 'nom', 'prenoms', 'email'];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function resultats()
    {
        return $this->hasMany(Resultat::class);
    }
}
