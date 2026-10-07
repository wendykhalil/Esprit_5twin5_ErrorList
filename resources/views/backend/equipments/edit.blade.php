@extends('layouts.backend')

@section('title', 'Modifier équipement - SolarShare Admin')

@section('content')

<div class="max-w-5xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">

        <div>

            <h2
                class="text-2xl font-bold text-slate-900"
                style="font-family: Outfit, sans-serif"
            >
                Modifier l'équipement
            </h2>

            <p
                class="text-sm text-slate-500 mt-1"
                style="font-family: Outfit, sans-serif"
            >
                Modifiez les informations de l'équipement depuis le BackOffice.
            </p>

        </div>

        <a
            href="{{ route('admin.equipments') }}"
            class="px-4 py-2 border border-slate-200
                   rounded-lg text-sm font-semibold
                   text-slate-600 hover:bg-slate-50
                   transition-colors"
            style="font-family: Outfit, sans-serif"
        >
            Retour
        </a>

    </div>


    {{-- Errors --}}
    @if($errors->any())

        <div
            class="bg-red-50 border border-red-200
                   text-red-700 rounded-xl p-4"
        >

            <p class="font-semibold mb-2">
                Veuillez corriger les erreurs suivantes :
            </p>

            <ul class="list-disc list-inside text-sm space-y-1">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- Form --}}
    <div
        class="bg-white border border-slate-200
               rounded-xl p-6 shadow-sm"
    >

        <form
            method="POST"
            action="{{ route('admin.equipments.update', $equipment) }}"
            enctype="multipart/form-data"
            class="space-y-6"
        >

            @csrf
            @method('PUT')


            {{-- Name --}}
            <div>

                <label
                    for="name"
                    class="block text-sm font-semibold
                           text-slate-700 mb-2"
                >
                    Nom
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    required
                    value="{{ old('name', $equipment->name) }}"
                    class="w-full px-4 py-3
                           border border-slate-200
                           rounded-lg focus:outline-none
                           focus:ring-2 focus:ring-amber-400"
                >

                @error('name')
                    <p class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror

            </div>


            {{-- Category --}}
            <div>

                <label
                    for="category_id"
                    class="block text-sm font-semibold
                           text-slate-700 mb-2"
                >
                    Catégorie
                </label>

                <select
                    id="category_id"
                    name="category_id"
                    required
                    class="w-full px-4 py-3
                           border border-slate-200
                           rounded-lg bg-white
                           focus:outline-none
                           focus:ring-2 focus:ring-amber-400"
                >

                    @foreach($categories as $category)

                        <option
                            value="{{ $category->id }}"
                            {{ old('category_id', $equipment->category_id) == $category->id ? 'selected' : '' }}
                        >
                            {{ $category->name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Brand --}}
            <div>

                <label
                    for="brand"
                    class="block text-sm font-semibold
                           text-slate-700 mb-2"
                >
                    Marque
                </label>

                <input
                    type="text"
                    id="brand"
                    name="brand"
                    value="{{ old('brand', $equipment->brand) }}"
                    class="w-full px-4 py-3
                           border border-slate-200
                           rounded-lg focus:outline-none
                           focus:ring-2 focus:ring-amber-400"
                >

            </div>


            {{-- Description --}}
            <div>

                <label
                    for="description"
                    class="block text-sm font-semibold
                           text-slate-700 mb-2"
                >
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    required
                    rows="5"
                    class="w-full px-4 py-3
                           border border-slate-200
                           rounded-lg resize-none
                           focus:outline-none
                           focus:ring-2 focus:ring-amber-400"
                >{{ old('description', $equipment->description) }}</textarea>

            </div>


            {{-- Power + Capacity --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>

                    <label
                        for="power"
                        class="block text-sm font-semibold
                               text-slate-700 mb-2"
                    >
                        Puissance (W)
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        id="power"
                        name="power"
                        value="{{ old('power', $equipment->power) }}"
                        class="w-full px-4 py-3
                               border border-slate-200
                               rounded-lg focus:outline-none
                               focus:ring-2 focus:ring-amber-400"
                    >

                </div>


                <div>

                    <label
                        for="capacity"
                        class="block text-sm font-semibold
                               text-slate-700 mb-2"
                    >
                        Capacité (Wh)
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        id="capacity"
                        name="capacity"
                        value="{{ old('capacity', $equipment->capacity) }}"
                        class="w-full px-4 py-3
                               border border-slate-200
                               rounded-lg focus:outline-none
                               focus:ring-2 focus:ring-amber-400"
                    >

                </div>

            </div>


            {{-- Condition --}}
            <div>

                <label
                    for="condition"
                    class="block text-sm font-semibold
                           text-slate-700 mb-2"
                >
                    État
                </label>

                <select
                    id="condition"
                    name="condition"
                    required
                    class="w-full px-4 py-3
                           border border-slate-200
                           rounded-lg bg-white
                           focus:outline-none
                           focus:ring-2 focus:ring-amber-400"
                >

                    <option
                        value="excellent"
                        {{ old('condition', $equipment->condition) === 'excellent' ? 'selected' : '' }}
                    >
                        Excellent
                    </option>

                    <option
                        value="good"
                        {{ old('condition', $equipment->condition) === 'good' ? 'selected' : '' }}
                    >
                        Bon
                    </option>

                    <option
                        value="used"
                        {{ old('condition', $equipment->condition) === 'used' ? 'selected' : '' }}
                    >
                        Utilisé
                    </option>

                </select>

            </div>


            {{-- Location --}}
            <div>

                <label
                    for="location"
                    class="block text-sm font-semibold
                           text-slate-700 mb-2"
                >
                    Localisation
                </label>

                <input
                    type="text"
                    id="location"
                    name="location"
                    required
                    value="{{ old('location', $equipment->location) }}"
                    class="w-full px-4 py-3
                           border border-slate-200
                           rounded-lg focus:outline-none
                           focus:ring-2 focus:ring-amber-400"
                >

            </div>


            {{-- Price --}}
            <div>

                <label
                    for="price_per_day"
                    class="block text-sm font-semibold
                           text-slate-700 mb-2"
                >
                    Prix par jour
                </label>

                <input
                    type="number"
                    step="0.01"
                    min="0"
                    id="price_per_day"
                    name="price_per_day"
                    required
                    value="{{ old('price_per_day', $equipment->price_per_day) }}"
                    class="w-full px-4 py-3
                           border border-slate-200
                           rounded-lg focus:outline-none
                           focus:ring-2 focus:ring-amber-400"
                >

            </div>


            {{-- Availability --}}
            <div
                class="border border-slate-200
                       rounded-lg p-4"
            >

                <label
                    class="flex items-center gap-3 cursor-pointer"
                >

                    <input
                        type="checkbox"
                        name="availability"
                        value="1"
                        {{ old('availability', $equipment->availability) ? 'checked' : '' }}
                        class="w-5 h-5 accent-amber-500"
                    >

                    <span
                        class="text-sm font-semibold
                               text-slate-700"
                    >
                        Disponible
                    </span>

                </label>

            </div>


            {{-- Status --}}
            <div>

                <label
                    for="status"
                    class="block text-sm font-semibold
                           text-slate-700 mb-2"
                >
                    Statut
                </label>

                <select
                    id="status"
                    name="status"
                    required
                    class="w-full px-4 py-3
                           border border-slate-200
                           rounded-lg bg-white
                           focus:outline-none
                           focus:ring-2 focus:ring-amber-400"
                >

                    <option
                        value="active"
                        {{ old('status', $equipment->status) === 'active' ? 'selected' : '' }}
                    >
                        Actif
                    </option>

                    <option
                        value="inactive"
                        {{ old('status', $equipment->status) === 'inactive' ? 'selected' : '' }}
                    >
                        Inactif
                    </option>

                </select>

            </div>


            {{-- Current Image --}}
            @if($equipment->image)

                <div>

                    <p
                        class="text-sm font-semibold
                               text-slate-700 mb-2"
                    >
                        Image actuelle
                    </p>

                    <img
                        src="{{ asset('storage/' . $equipment->image) }}"
                        alt="{{ $equipment->name }}"
                        class="w-48 h-32 object-cover
                               rounded-lg border
                               border-slate-200"
                    >

                </div>

            @endif


            {{-- New image --}}
            <div>

                <label
                    for="image"
                    class="block text-sm font-semibold
                           text-slate-700 mb-2"
                >
                    Nouvelle image
                </label>

                <input
                    type="file"
                    id="image"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp"
                    class="w-full text-sm
                           border border-slate-200
                           rounded-lg bg-white"
                >

                <p
                    class="text-xs text-slate-400 mt-2"
                >
                    Laissez vide pour conserver l'image actuelle.
                </p>

            </div>


            {{-- Buttons --}}
            <div
                class="flex flex-col sm:flex-row
                       gap-3 pt-5 border-t border-slate-200"
            >

                <a
                    href="{{ route('admin.equipments') }}"
                    class="flex-1 text-center px-5 py-3
                           border border-slate-200
                           rounded-lg text-sm font-semibold
                           text-slate-600 hover:bg-slate-50"
                >
                    Annuler
                </a>


                <button
                    type="submit"
                    class="flex-1 px-5 py-3
                           bg-amber-500 text-white
                           rounded-lg text-sm font-semibold
                           hover:bg-amber-600 transition-colors"
                >
                    Enregistrer les modifications
                </button>

            </div>

        </form>

    </div>

</div>

@endsection