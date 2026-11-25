<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    protected $fillable = ['departement_id', 'libelle', 'code'];

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }
}
