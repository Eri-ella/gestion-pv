<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    protected $fillable = ['libelle', 'code', 'actif'];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    public function filieres()
    {
        return $this->hasMany(Filiere::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
