@if (session('mensaje'))
    <div class="mb-6 flex items-center gap-3 rounded-xl border border-uch-teal/30 bg-uch-teal/10 px-4 py-3 text-body-sm text-[#007A70]">
        <span class="material-symbols-outlined text-[20px]">check_circle</span>
        <span class="font-semibold">{{ session('mensaje') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="mb-6 flex items-center gap-3 rounded-xl border border-uch-coral/30 bg-uch-coral/10 px-4 py-3 text-body-sm text-uch-coral-dark">
        <span class="material-symbols-outlined text-[20px]">error</span>
        <span class="font-semibold">{{ session('error') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-xl border border-uch-coral/30 bg-uch-coral/10 px-4 py-3 text-body-sm text-uch-coral-dark">
        <div class="flex items-center gap-2 font-semibold mb-1">
            <span class="material-symbols-outlined text-[20px]">warning</span>
            Revisa los campos marcados.
        </div>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
