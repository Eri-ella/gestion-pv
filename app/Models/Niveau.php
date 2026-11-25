<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Niveau extends Model
{
    protected $fillable = ['libelle', 'ordre'];

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }
}
