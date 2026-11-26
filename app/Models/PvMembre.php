<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PvMembre extends Model
{
    protected $fillable = ['pv_id', 'nom', 'fonction', 'president'];

    protected function casts(): array
    {
        return ['president' => 'boolean'];
    }

    public function pv()
    {
        return $this->belongsTo(Pv::class);
    }
}
