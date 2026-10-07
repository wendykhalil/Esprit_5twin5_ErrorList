@php($i = $inspection ?? null)
<div class="mb-3">
    <label for="reservation_id">Réservation</label>
    <select name="reservation_id" id="reservation_id" class="form-control">
        <option value="">-- Choisir --</option>
        @foreach ($reservations as $reservation)
            <option value="{{ $reservation->id }}" @selected(old('reservation_id', $i?->reservation_id) == $reservation->id)>
                #{{ $reservation->id }} - {{ $reservation->equipement->nom ?? 'Équipement #'.$reservation->equipement_id }}
            </option>
        @endforeach
    </select>
    @error('reservation_id') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label for="type">Type d'inspection</label>
    <select name="type" id="type" class="form-control">
        @foreach ($types as $type)
            <option value="{{ $type }}" @selected(old('type', $i?->type) === $type)>{{ ucfirst($type) }}</option>
        @endforeach
    </select>
    @error('type') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label for="etat">État de l'équipement</label>
    <select name="etat" id="etat" class="form-control">
        @foreach ($etats as $etat)
            <option value="{{ $etat }}" @selected(old('etat', $i?->etat) === $etat)>{{ ucfirst($etat) }}</option>
        @endforeach
    </select>
    @error('etat') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label for="niveau_batterie">Niveau de batterie (%)</label>
    <input type="number" name="niveau_batterie" id="niveau_batterie" min="0" max="100" class="form-control"
           value="{{ old('niveau_batterie', $i?->niveau_batterie) }}">
    @error('niveau_batterie') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label for="observations">Observations</label>
    <textarea name="observations" id="observations" rows="3" class="form-control">{{ old('observations', $i?->observations) }}</textarea>
    @error('observations') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label for="date_inspection">Date de l'inspection</label>
    <input type="datetime-local" name="date_inspection" id="date_inspection" class="form-control"
           value="{{ old('date_inspection', $i?->date_inspection?->format('Y-m-d\TH:i')) }}">
    @error('date_inspection') <small class="text-danger">{{ $message }}</small> @enderror
</div>
