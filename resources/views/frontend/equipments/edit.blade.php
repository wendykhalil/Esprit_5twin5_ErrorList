@extends('layouts.frontend')

@section('title', 'Modifier ' . $equipment->name . ' - SolarShare')

@section('content')

<div class="bg-gray-50 py-12">

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8">

            <a
                href="{{ route('equipments.show', $equipment) }}"
                class="inline-flex items-center gap-2 text-green-600 hover:text-green-700 mb-6"
            >
                ← Retour à l'équipement
            </a>

            <h1
                class="text-4xl font-bold text-green-900 mb-2"
                style="font-family: Fraunces, Georgia, serif"
            >
                Modifier l'équipement
            </h1>

            <p class="text-gray-600 text-lg">
                Modifiez les informations de votre équipement SolarShare.
            </p>

        </div>


        {{-- Validation errors --}}
        @if ($errors->any())

            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-5">

                <p class="font-semibold text-red-800 mb-2">
                    Veuillez corriger les erreurs suivantes :
                </p>

                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- Form --}}
        <div
            class="bg-white rounded-2xl shadow-md
                   border border-gray-200 p-8"
        >

            <form
                method="POST"
                action="{{ route('equipments.update', $equipment) }}"
                enctype="multipart/form-data"
                class="space-y-6"
            >

                @csrf
                @method('PUT')


                {{-- Name --}}
                <div>

                    <label
                        for="name"
                        class="block text-sm font-semibold text-gray-700 mb-2"
                    >
                        Nom de l'équipement
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        required
                        value="{{ old('name', $equipment->name) }}"
                        class="w-full px-4 py-3 border rounded-lg
                               focus:outline-none focus:ring-2 focus:ring-green-500
                               @error('name') border-red-500
                               @else border-gray-300
                               @enderror"
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
                        class="block text-sm font-semibold text-gray-700 mb-2"
                    >
                        Catégorie
                        <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="category_id"
                        name="category_id"
                        required
                        class="w-full px-4 py-3 border border-gray-300
                               rounded-lg bg-white
                               focus:outline-none focus:ring-2 focus:ring-green-500"
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

                    @error('category_id')

                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                </div>


                {{-- Brand --}}
                <div>

                    <label
                        for="brand"
                        class="block text-sm font-semibold text-gray-700 mb-2"
                    >
                        Marque
                    </label>

                    <input
                        type="text"
                        id="brand"
                        name="brand"
                        value="{{ old('brand', $equipment->brand) }}"
                        class="w-full px-4 py-3 border border-gray-300
                               rounded-lg focus:outline-none
                               focus:ring-2 focus:ring-green-500"
                    >

                    @error('brand')

                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                </div>


                {{-- Description --}}
                <div>

                    <label
                        for="description"
                        class="block text-sm font-semibold text-gray-700 mb-2"
                    >
                        Description
                        <span class="text-red-500">*</span>
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        required
                        class="w-full px-4 py-3 border rounded-lg
                               resize-none focus:outline-none
                               focus:ring-2 focus:ring-green-500
                               @error('description') border-red-500
                               @else border-gray-300
                               @enderror"
                    >{{ old('description', $equipment->description) }}</textarea>

                    @error('description')

                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                </div>


                {{-- Power + Capacity --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div>

                        <label
                            for="power"
                            class="block text-sm font-semibold text-gray-700 mb-2"
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
                            class="w-full px-4 py-3 border border-gray-300
                                   rounded-lg focus:outline-none
                                   focus:ring-2 focus:ring-green-500"
                        >

                        @error('power')

                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>


                    <div>

                        <label
                            for="capacity"
                            class="block text-sm font-semibold text-gray-700 mb-2"
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
                            class="w-full px-4 py-3 border border-gray-300
                                   rounded-lg focus:outline-none
                                   focus:ring-2 focus:ring-green-500"
                        >

                        @error('capacity')

                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>

                </div>


                {{-- Condition --}}
                <div>

                    <label
                        for="condition"
                        class="block text-sm font-semibold text-gray-700 mb-2"
                    >
                        État
                        <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="condition"
                        name="condition"
                        required
                        class="w-full px-4 py-3 border border-gray-300
                               rounded-lg bg-white focus:outline-none
                               focus:ring-2 focus:ring-green-500"
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

                    @error('condition')

                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                </div>


                {{-- Location --}}
                <div>

                    <label
                        for="location"
                        class="block text-sm font-semibold text-gray-700 mb-2"
                    >
                        Localisation
                        <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="location"
                        name="location"
                        required
                        class="w-full px-4 py-3 border border-gray-300
                               rounded-lg bg-white focus:outline-none
                               focus:ring-2 focus:ring-green-500"
                    >

                        @foreach([
                            'Tunis',
                            'Ariana',
                            'Ben Arous',
                            'Manouba',
                            'Nabeul',
                            'Bizerte',
                            'Sousse',
                            'Monastir',
                            'Mahdia',
                            'Sfax',
                            'Gabès',
                            'Médenine',
                            'Tataouine'
                        ] as $city)

                            <option
                                value="{{ $city }}"
                                {{ old('location', $equipment->location) === $city ? 'selected' : '' }}
                            >
                                {{ $city }}
                            </option>

                        @endforeach

                    </select>

                    @error('location')

                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                </div>


                {{-- Price --}}
                <div>

                    <label
                        for="price_per_day"
                        class="block text-sm font-semibold text-gray-700 mb-2"
                    >
                        Prix par jour
                        <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            id="price_per_day"
                            name="price_per_day"
                            required
                            value="{{ old('price_per_day', $equipment->price_per_day) }}"
                            class="w-full px-4 py-3 pr-16 border border-gray-300
                                   rounded-lg focus:outline-none
                                   focus:ring-2 focus:ring-green-500"
                        >

                        <span
                            class="absolute right-3 top-3
                                   text-gray-500 font-medium"
                        >
                            TND
                        </span>

                    </div>

                    @error('price_per_day')

                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                </div>


                {{-- Availability --}}
                <div
                    class="border border-gray-200
                           rounded-xl p-4"
                >

                    <label
                        class="flex items-center gap-3 cursor-pointer"
                    >

                        <input
                            type="checkbox"
                            name="availability"
                            value="1"
                            {{ old('availability', $equipment->availability) ? 'checked' : '' }}
                            class="w-5 h-5 text-green-600 rounded
                                   border-gray-300 focus:ring-green-500"
                        >

                        <div>

                            <p class="font-semibold text-gray-800">
                                Disponible à la location
                            </p>

                            <p class="text-sm text-gray-500">
                                Décochez si l'équipement n'est actuellement pas disponible.
                            </p>

                        </div>

                    </label>

                </div>


                {{-- Status --}}
                <div>

                    <label
                        for="status"
                        class="block text-sm font-semibold text-gray-700 mb-2"
                    >
                        Statut
                    </label>

                    <select
                        id="status"
                        name="status"
                        required
                        class="w-full px-4 py-3 border border-gray-300
                               rounded-lg bg-white focus:outline-none
                               focus:ring-2 focus:ring-green-500"
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

                        <p class="block text-sm font-semibold text-gray-700 mb-2">
                            Photo actuelle
                        </p>

                        <img
                            src="{{ asset('storage/' . $equipment->image) }}"
                            alt="{{ $equipment->name }}"
                            class="w-full max-h-72 object-contain
                                   bg-gray-100 rounded-xl border"
                        >

                    </div>

                @endif


                {{-- New Image --}}
                <div>

                    <label
                        for="image"
                        class="block text-sm font-semibold text-gray-700 mb-2"
                    >
                        Remplacer la photo
                    </label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="block w-full text-sm text-gray-700
                               border border-gray-300 rounded-lg
                               cursor-pointer bg-white"
                    >

                    <p class="text-xs text-gray-500 mt-2">
                        Laissez vide pour conserver la photo actuelle.
                    </p>

                    @error('image')

                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                </div>


                {{-- Buttons --}}
                <div
                    class="flex flex-col sm:flex-row gap-3
                           pt-6 border-t border-gray-200"
                >

                    <a
                        href="{{ route('equipments.show', $equipment) }}"
                        class="flex-1 text-center px-6 py-3
                               border border-gray-300 text-gray-700
                               font-semibold rounded-lg
                               hover:bg-gray-100 transition"
                    >
                        Annuler
                    </a>

                    <button
                        type="submit"
                        class="flex-1 px-6 py-3
                               bg-green-600 hover:bg-green-700
                               text-white font-semibold
                               rounded-lg transition"
                    >
                        Enregistrer les modifications
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection