@csrf
<div class="space-y-5">
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-semibold">Nom</label>
            <input name="nom" value="{{ old('nom', $badge->nom ?? '') }}" required class="mt-2 w-full rounded-lg border-slate-200">
        </div>
        <div>
            <label class="block text-sm font-semibold">Slug</label>
            <input name="slug" value="{{ old('slug', $badge->slug ?? '') }}" required class="mt-2 w-full rounded-lg border-slate-200">
        </div>
    </div>
    <div>
        <label class="block text-sm font-semibold">Description</label>
        <textarea name="description" rows="3" required class="mt-2 w-full rounded-lg border-slate-200">{{ old('description', $badge->description ?? '') }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-semibold">Illustration</label>
        <input name="image" type="file" accept=".jpg,.jpeg,.png,.webp,.svg" class="mt-2 w-full rounded-lg border-slate-200">
        <p class="mt-1 text-xs text-slate-500">JPG, PNG, WEBP ou SVG, 2 Mo maximum.</p>
    </div>
    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <label class="block text-sm font-semibold">Critère</label>
            <select name="critere" required class="mt-2 w-full rounded-lg border-slate-200">
                @foreach(['kwh_total' => 'kWh total', 'co2_total' => 'CO2 total', 'reservations_terminees' => 'Réservations terminées', 'retours_sans_dommage' => 'Retours sans dommage', 'releves_declares' => 'Relevés déclarés'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('critere', $badge->critere ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold">Seuil</label>
            <input name="seuil" type="number" min="0" value="{{ old('seuil', $badge->seuil ?? '') }}" required class="mt-2 w-full rounded-lg border-slate-200">
        </div>
        <div>
            <label class="block text-sm font-semibold">Niveau</label>
            <select name="niveau" required class="mt-2 w-full rounded-lg border-slate-200">
                @foreach(['bronze', 'argent', 'or'] as $level)
                    <option value="{{ $level }}" @selected(old('niveau', $badge->niveau ?? '') === $level)>{{ ucfirst($level) }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <label class="flex items-center gap-2 text-sm font-semibold">
        <input name="actif" type="checkbox" value="1" @checked(old('actif', $badge->actif ?? true)) class="rounded border-slate-300 text-green-600">
        Badge actif dans le catalogue
    </label>
    <button class="rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white">
        {{ isset($badge) ? 'Enregistrer' : 'Ajouter' }}
    </button>
</div>
