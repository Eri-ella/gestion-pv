<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnneeUniversitaire extends Model
{
    protected $fillable = ['libelle', 'courante'];

    protected function casts(): array
    {
        return ['courante' => 'boolean'];
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }
}
