<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resultat extends Model
{
    protected $fillable = ['pv_id', 'etudiant_id', 'moyenne', 'credits_valides', 'decision'];

    protected function casts(): array
    {
        return [
            'moyenne' => 'decimal:2',
            'credits_valides' => 'decimal:2',
        ];
    }

    public function pv()
    {
        return $this->belongsTo(Pv::class);
    }

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }
}
