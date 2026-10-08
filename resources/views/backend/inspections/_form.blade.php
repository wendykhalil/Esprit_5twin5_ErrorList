@php($currentInspection = $inspection ?? null)
<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="reservation_id" class="mb-2 block text-sm font-600 text-slate-700">Réservation</label>
        <select id="reservation_id" name="reservation_id" class="w-full rounded-lg border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
            <option value="">Choisir une réservation</option>
            @foreach($reservations as $reservation)
                <option value="{{ $reservation->id }}" @selected(old('reservation_id', $reservation_id ?? $currentInspection?->reservation_id) == $reservation->id)>{{ $reservation->equipment_label }} — {{ $reservation->user->name }} — {{ $reservation->date_debut->format('d/m/Y') }} au {{ $reservation->date_fin->format('d/m/Y') }}</option>
            @endforeach
        </select>
        @error('reservation_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="type" class="mb-2 block text-sm font-600 text-slate-700">Type</label>
        <select id="type" name="type" class="w-full rounded-lg border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
            @foreach($types as $type)<option value="{{ $type }}" @selected(old('type', $currentInspection?->type) === $type)>{{ ucfirst($type) }}</option>@endforeach
        </select>
        @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="etat" class="mb-2 block text-sm font-600 text-slate-700">État</label>
        <select id="etat" name="etat" class="w-full rounded-lg border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
            @foreach($states as $state)<option value="{{ $state }}" @selected(old('etat', $currentInspection?->etat) === $state)>{{ ucfirst($state) }}</option>@endforeach
        </select>
        @error('etat')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="niveau_batterie" class="mb-2 block text-sm font-600 text-slate-700">Batterie (%)</label>
        <input id="niveau_batterie" type="number" min="0" max="100" name="niveau_batterie" value="{{ old('niveau_batterie', $currentInspection?->niveau_batterie) }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
        @error('niveau_batterie')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="date_inspection" class="mb-2 block text-sm font-600 text-slate-700">Date de l'inspection</label>
        <input id="date_inspection" type="datetime-local" name="date_inspection" value="{{ old('date_inspection', $currentInspection?->date_inspection?->format('Y-m-d\TH:i')) }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
        @error('date_inspection')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="observations" class="mb-2 block text-sm font-600 text-slate-700">Observations</label>
        <textarea id="observations" name="observations" rows="4" class="w-full rounded-lg border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">{{ old('observations', $currentInspection?->observations) }}</textarea>
        @error('observations')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>