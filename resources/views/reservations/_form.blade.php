@php($r = $reservation ?? null)
<div class="mb-3">
    <label for="user_id">Utilisateur</label>
    <select name="user_id" id="user_id" class="form-control">
        <option value="">-- Choisir --</option>
        @foreach ($users as $user)
            <option value="{{ $user->id }}" @selected(old('user_id', $r?->user_id) == $user->id)>{{ $user->name }}</option>
        @endforeach
    </select>
    @error('user_id') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label for="equipement_id">Équipement</label>
    <select name="equipement_id" id="equipement_id" class="form-control">
        <option value="">-- Choisir --</option>
        @foreach ($equipements as $equipement)
            <option value="{{ $equipement->id }}" @selected(old('equipement_id', $r?->equipement_id) == $equipement->id)>
                {{ $equipement->nom ?? 'Équipement #'.$equipement->id }}
            </option>
        @endforeach
    </select>
    @error('equipement_id') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label for="date_debut">Date de début</label>
    <input type="date" name="date_debut" id="date_debut" class="form-control"
           value="{{ old('date_debut', $r?->date_debut?->format('Y-m-d')) }}">
    @error('date_debut') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label for="date_fin">Date de fin</label>
    <input type="date" name="date_fin" id="date_fin" class="form-control"
           value="{{ old('date_fin', $r?->date_fin?->format('Y-m-d')) }}">
    @error('date_fin') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
    <label for="statut">Statut</label>
    <select name="statut" id="statut" class="form-control">
        @foreach ($statuts as $statut)
            <option value="{{ $statut }}" @selected(old('statut', $r?->statut ?? 'en_attente') === $statut)>{{ ucfirst(str_replace('_', ' ', $statut)) }}</option>
        @endforeach
    </select>
    @error('statut') <small class="text-danger">{{ $message }}</small> @enderror
</div>
