<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvenementJournal extends Model
{
    protected $fillable = ['user_id', 'action', 'cible', 'details', 'ip'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
