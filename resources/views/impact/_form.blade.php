@csrf
@if(isset($releve)) @method('PUT')<input type="hidden" name="reservation_id" value="{{ $releve->reservation_id }}">@endif
@php
    $maxHours = isset($releve) ? max(1, $releve->reservation->date_debut->diffInDays($releve->reservation->date_fin) * 24) : null;
@endphp
<div class="space-y-5">
    @if(!isset($releve))
    <div><label class="block text-sm font-semibold text-slate-700">Réservation terminée</label><select name="reservation_id" required class="mt-2 w-full rounded-lg border-slate-200">@foreach($reservations as $reservation)<option value="{{ $reservation->id }}" @selected(old('reservation_id', request('reservation_id')) == $reservation->id)>{{ $reservation->equipment_label }} — {{ $reservation->date_debut->format('d/m/Y') }} → {{ $reservation->date_fin->format('d/m/Y') }} ({{ $reservation->equipment?->power ?? 'n/c' }} W)</option>@endforeach</select>@error('reservation_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
    @else<div class="rounded-lg bg-slate-50 p-4 text-sm text-slate-700"><strong>{{ $releve->reservation->equipment_label }}</strong> — puissance : {{ $releve->puissance_watts ?? $releve->reservation->equipment?->power ?? 'n/c' }} W</div>@endif
    <div><label class="block text-sm font-semibold text-slate-700">Heures d’utilisation</label><input name="heures_utilisation" type="number" step="0.01" min="0.1" @if($maxHours) max="{{ $maxHours }}" @endif value="{{ old('heures_utilisation', $releve->heures_utilisation ?? '') }}" required class="mt-2 w-full rounded-lg border-slate-200">@error('heures_utilisation')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
    <div><label class="block text-sm font-semibold text-slate-700">Date du relevé</label><input name="date_releve" type="date" max="{{ now()->toDateString() }}" value="{{ old('date_releve', isset($releve) ? $releve->date_releve->toDateString() : '') }}" required class="mt-2 w-full rounded-lg border-slate-200">@error('date_releve')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
    <div><label class="block text-sm font-semibold text-slate-700">Observations</label><textarea name="observations" rows="4" class="mt-2 w-full rounded-lg border-slate-200">{{ old('observations', $releve->observations ?? '') }}</textarea>@error('observations')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
    <button class="rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700">{{ isset($releve) ? 'Enregistrer les modifications' : 'Enregistrer le relevé' }}</button>
</div>
