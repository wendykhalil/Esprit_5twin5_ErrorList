@php($currentReservation = $reservation ?? null)
<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label for="user_id" class="mb-2 block text-sm font-600 text-slate-700">Utilisateur</label>
        <select id="user_id" name="user_id" class="w-full rounded-lg border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
            <option value="">Choisir un utilisateur</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected(old('user_id', $currentReservation?->user_id) == $user->id)>{{ $user->name }} — {{ $user->email }}</option>
            @endforeach
        </select>
        @error('user_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="equipment_id" class="mb-2 block text-sm font-600 text-slate-700">Équipement</label>
        <select id="equipment_id" name="equipment_id" class="w-full rounded-lg border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
            <option value="">Choisir un équipement</option>
            @foreach($equipments as $equipment)
                <option value="{{ $equipment->id }}" @selected(old('equipment_id', $currentReservation?->equipment_id) == $equipment->id)>{{ $equipment->name }}</option>
            @endforeach
        </select>
        @error('equipment_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="date_debut" class="mb-2 block text-sm font-600 text-slate-700">Date de début</label>
        <input id="date_debut" type="date" name="date_debut" value="{{ old('date_debut', $currentReservation?->date_debut?->format('Y-m-d')) }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
        @error('date_debut') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="date_fin" class="mb-2 block text-sm font-600 text-slate-700">Date de fin</label>
        <input id="date_fin" type="date" name="date_fin" value="{{ old('date_fin', $currentReservation?->date_fin?->format('Y-m-d')) }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
        @error('date_fin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="statut" class="mb-2 block text-sm font-600 text-slate-700">Statut</label>
        <select id="statut" name="statut" class="w-full rounded-lg border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(old('statut', $currentReservation?->statut ?? 'en_attente') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        @error('statut') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>