@if (session('success'))
    <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        {{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <p class="font-medium">Le formulaire contient des erreurs :</p>
        <ul class="mt-1 list-inside list-disc">
            @foreach ($errors->all() as $e)
            <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif