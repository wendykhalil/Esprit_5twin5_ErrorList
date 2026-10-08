@php
    $i = $inspection ?? null;
    $typeLabels = ['remise' => 'Remise', 'retour' => 'Retour'];
    $stateLabels = ['neuf' => 'Neuf', 'bon' => 'Bon', 'use' => 'Usé', 'endommage' => 'Endommagé'];
@endphp

<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="reservation_id" class="mb-2 block text-sm font-semibold text-slate-700">Réservation</label>
        <select name="reservation_id" id="reservation_id" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500">
            <option value="">Choisir une réservation</option>
            @foreach ($reservations as $reservation)
                <option value="{{ $reservation->id }}" @selected(old('reservation_id', $reservation_id ?? $i?->reservation_id) == $reservation->id)>
                    {{ $reservation->equipment_label }} - du {{ $reservation->date_debut->format('d/m/Y') }} au {{ $reservation->date_fin->format('d/m/Y') }}
                </option>
            @endforeach
        </select>
        @error('reservation_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="type" class="mb-2 block text-sm font-semibold text-slate-700">Type d'inspection</label>
        <select name="type" id="type" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500">
            @foreach ($types as $type)
                <option value="{{ $type }}" @selected(old('type', $i?->type) === $type)>{{ $typeLabels[$type] ?? ucfirst($type) }}</option>
            @endforeach
        </select>
        @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="etat" class="mb-2 block text-sm font-semibold text-slate-700">État de l'équipement</label>
        <select name="etat" id="etat" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500">
            @foreach ($etats as $etat)
                <option value="{{ $etat }}" @selected(old('etat', $i?->etat) === $etat)>{{ $stateLabels[$etat] ?? ucfirst($etat) }}</option>
            @endforeach
        </select>
        @error('etat') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="niveau_batterie" class="mb-2 block text-sm font-semibold text-slate-700">Niveau de batterie (%)</label>
        <input type="number" name="niveau_batterie" id="niveau_batterie" min="0" max="100" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500" value="{{ old('niveau_batterie', $i?->niveau_batterie) }}">
        @error('niveau_batterie') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="date_inspection" class="mb-2 block text-sm font-semibold text-slate-700">Date de l'inspection</label>
        <input type="datetime-local" name="date_inspection" id="date_inspection" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500" value="{{ old('date_inspection', $i?->date_inspection?->format('Y-m-d\TH:i')) }}">
        @error('date_inspection') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="observations" class="mb-2 block text-sm font-semibold text-slate-700">Observations</label>
        <textarea name="observations" id="observations" rows="4" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500">{{ old('observations', $i?->observations) }}</textarea>
        @error('observations') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
