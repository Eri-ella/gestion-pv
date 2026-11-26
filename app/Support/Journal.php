<?php

namespace App\Support;

use App\Models\EvenementJournal;
use Illuminate\Support\Facades\Auth;

class Journal
{
    public static function enregistrer(
        string $action,
        ?string $cible = null,
        ?string $details = null,
    ): EvenementJournal {
        return EvenementJournal::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'cible' => $cible,
            'details' => $details,
            'ip' => request()->ip(),
        ]);
    }
}
