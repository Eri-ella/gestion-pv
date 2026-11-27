@if (session('success'))
    <div class="mb-4 rounded-lg border border-primary/30 bg-primary-soft px-4 py-3 text-sm text-primary-dark">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="mb-4 rounded-lg border border-danger/30 bg-danger-soft px-4 py-3 text-sm text-danger">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="mb-4 rounded-lg border border-danger/30 bg-danger-soft px-4 py-3 text-sm text-danger">
        <p class="font-medium">Le formulaire contient des erreurs :</p>
        <ul class="mt-1 list-inside list-disc">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif
