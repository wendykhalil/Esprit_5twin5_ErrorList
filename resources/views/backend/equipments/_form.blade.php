@php
    $isEdit = isset($equipment) && $equipment?->exists;
@endphp

{{-- Name --}}
<div>
    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">
        Nom <span class="text-red-500">*</span>
    </label>
    <input
        type="text"
        id="name"
        name="name"
        value="{{ old('name', $equipment?->name ?? '') }}"
        autocomplete="off"
        aria-describedby="name-hint @error('name') name-error @enderror"
        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 @error('name') border-red-400 @else border-slate-200 @enderror"
    >
    <p id="name-hint" class="mt-1 text-xs text-slate-500">3 à 255 caractères.</p>
    @error('name')
        <p id="name-error" class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Category --}}
<div>
    <label for="category_id" class="block text-sm font-semibold text-slate-700 mb-2">
        Catégorie <span class="text-red-500">*</span>
    </label>
    <select
        id="category_id"
        name="category_id"
        aria-describedby="category_id-hint @error('category_id') category_id-error @enderror"
        class="w-full px-4 py-3 border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 @error('category_id') border-red-400 @else border-slate-200 @enderror"
    >
        @unless($isEdit)
            <option value="" @selected(old('category_id') === null || old('category_id') === '')>Sélectionner une catégorie</option>
        @endunless
        @foreach ($categories as $category)
            <option
                value="{{ $category->id }}"
                @selected(old('category_id', $equipment?->category_id ?? '') == $category->id)
            >
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    <p id="category_id-hint" class="mt-1 text-xs text-slate-500">Liste des catégories enregistrées dans le catalogue.</p>
    @error('category_id')
        <p id="category_id-error" class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Brand --}}
<div>
    <label for="brand" class="block text-sm font-semibold text-slate-700 mb-2">Marque</label>
    <input
        type="text"
        id="brand"
        name="brand"
        value="{{ old('brand', $equipment?->brand ?? '') }}"
        class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 @error('brand') border-red-400 @enderror"
    >
    <p class="mt-1 text-xs text-slate-500">Optionnel — 255 caractères maximum.</p>
    @error('brand')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Description --}}
<div>
    <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">
        Description <span class="text-red-500">*</span>
    </label>
    <textarea
        id="description"
        name="description"
        rows="5"
        aria-describedby="description-hint description-count @error('description') description-error @enderror"
        class="w-full px-4 py-3 border rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-amber-400 @error('description') border-red-400 @else border-slate-200 @enderror"
    >{{ old('description', $equipment?->description ?? '') }}</textarea>
    <div class="mt-1 flex flex-wrap items-center justify-between gap-2">
        <p id="description-hint" class="text-xs text-slate-500">10 à 1000 caractères — état, accessoires, défauts visibles.</p>
        <p id="description-count" class="text-xs text-slate-400" aria-live="polite">0 / 1000</p>
    </div>
    @error('description')
        <p id="description-error" class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Power + Capacity --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label for="power" class="block text-sm font-semibold text-slate-700 mb-2">Puissance (W)</label>
        <input
            type="number"
            step="0.01"
            id="power"
            name="power"
            value="{{ old('power', $equipment?->power ?? '') }}"
            class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 @error('power') border-red-400 @enderror"
        >
        <p class="mt-1 text-xs text-slate-500">Optionnel — nombre ≥ 0.</p>
        @error('power')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label for="capacity" class="block text-sm font-semibold text-slate-700 mb-2">Capacité (Wh)</label>
        <input
            type="number"
            step="0.01"
            id="capacity"
            name="capacity"
            value="{{ old('capacity', $equipment?->capacity ?? '') }}"
            class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 @error('capacity') border-red-400 @enderror"
        >
        <p class="mt-1 text-xs text-slate-500">Optionnel — nombre ≥ 0.</p>
        @error('capacity')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- Condition --}}
<div>
    <label for="condition" class="block text-sm font-semibold text-slate-700 mb-2">
        État <span class="text-red-500">*</span>
    </label>
    <select
        id="condition"
        name="condition"
        class="w-full px-4 py-3 border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 @error('condition') border-red-400 @else border-slate-200 @enderror"
    >
        <option value="excellent" @selected(old('condition', $equipment?->condition ?? 'good') === 'excellent')>Excellent</option>
        <option value="good" @selected(old('condition', $equipment?->condition ?? 'good') === 'good')>Bon</option>
        <option value="used" @selected(old('condition', $equipment?->condition ?? '') === 'used')>Utilisé</option>
    </select>
    @error('condition')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Location --}}
<div>
    <label for="location" class="block text-sm font-semibold text-slate-700 mb-2">
        Localisation <span class="text-red-500">*</span>
    </label>
    <input
        type="text"
        id="location"
        name="location"
        value="{{ old('location', $equipment?->location ?? '') }}"
        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 @error('location') border-red-400 @else border-slate-200 @enderror"
    >
    <p class="mt-1 text-xs text-slate-500">Ville ou zone de remise de l'équipement.</p>
    @error('location')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Price --}}
<div>
    <label for="price_per_day" class="block text-sm font-semibold text-slate-700 mb-2">
        Prix par jour (TND) <span class="text-red-500">*</span>
    </label>
    <input
        type="number"
        step="0.01"
        id="price_per_day"
        name="price_per_day"
        value="{{ old('price_per_day', $equipment?->price_per_day ?? '') }}"
        aria-describedby="price_per_day-hint @error('price_per_day') price_per_day-error @enderror"
        class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400 @error('price_per_day') border-red-400 @else border-slate-200 @enderror"
    >
    <p id="price_per_day-hint" class="mt-1 text-xs text-slate-500">Tarif journalier en dinars, entre 0 et 99 999 TND.</p>
    @error('price_per_day')
        <p id="price_per_day-error" class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

{{-- Availability --}}
<div class="border border-slate-200 rounded-lg p-4">
    <label class="flex items-center gap-3 cursor-pointer">
        <input
            type="checkbox"
            name="availability"
            value="1"
            @checked(old('availability', $equipment?->availability ?? true))
            class="w-5 h-5 accent-amber-500"
        >
        <span class="text-sm font-semibold text-slate-700">Disponible à la location</span>
    </label>
</div>

{{-- Status --}}
<div>
    <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">
        Statut <span class="text-red-500">*</span>
    </label>
    <select
        id="status"
        name="status"
        class="w-full px-4 py-3 border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 @error('status') border-red-400 @else border-slate-200 @enderror"
    >
        <option value="active" @selected(old('status', $equipment?->status ?? 'active') === 'active')>Actif</option>
        <option value="inactive" @selected(old('status', $equipment?->status ?? '') === 'inactive')>Inactif</option>
    </select>
    @error('status')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

@if($isEdit && $equipment?->image)
    <div>
        <p class="text-sm font-semibold text-slate-700 mb-2">Image actuelle</p>
        <img
            src="{{ asset('storage/' . $equipment?->image) }}"
            alt="{{ $equipment?->name }}"
            class="w-48 h-32 object-cover rounded-lg border border-slate-200"
        >
    </div>
@endif

{{-- Image --}}
<div>
    <label for="image" class="block text-sm font-semibold text-slate-700 mb-2">
        {{ $isEdit ? 'Nouvelle image' : 'Photo' }}
    </label>
    <input
        type="file"
        id="image"
        name="image"
        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
        class="w-full text-sm border border-slate-200 rounded-lg bg-white @error('image') border-red-400 @enderror"
    >
    <p class="text-xs text-slate-400 mt-2">
        {{ $isEdit ? 'Laissez vide pour conserver l\'image actuelle. ' : '' }}JPG, PNG ou WEBP — 2 Mo maximum.
    </p>
    @error('image')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>
