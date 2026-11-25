<?php

namespace App\Http\Controllers;

use App\Models\EvenementJournal;

class JournalController extends Controller
{
    public function index()
    {
        $evenements = EvenementJournal::query()
            ->with('user')
            ->when(request('action'), fn ($q, $action) => $q->where('action', $action))
            ->when(request('date'), fn ($q, $date) => $q->whereDate('created_at', $date))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $actions = EvenementJournal::query()
            ->distinct()
            ->whereNotNull('action')
            ->orderBy('action')
            ->pluck('action');

        return view('journal.index', [
            'evenements' => $evenements,
            'actions' => $actions,
            'action' => request('action'),
            'date' => request('date'),
        ]);
    }
}
