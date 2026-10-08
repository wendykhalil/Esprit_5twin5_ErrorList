@extends('layouts.backend')

@section('title', 'Modifier équipement - SolarShare Admin')

@section('content')

<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">
                Modifier l'équipement
            </h2>
            <p class="text-sm text-slate-500 mt-1" style="font-family: Outfit, sans-serif">
                Modifiez les informations de l'équipement depuis le back-office.
            </p>
        </div>
        <a
            href="{{ route('admin.equipments') }}"
            class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors"
            style="font-family: Outfit, sans-serif"
        >
            Retour
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4" role="alert">
            <p class="font-semibold mb-2">Veuillez corriger les erreurs suivantes :</p>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        <form
            method="POST"
            action="{{ route('admin.equipments.update', $equipment) }}"
            enctype="multipart/form-data"
            class="space-y-6"
            novalidate
        >
            @csrf
            @method('PUT')

            @include('backend.equipments._form', ['equipment' => $equipment])

            <div class="flex flex-col sm:flex-row gap-3 pt-5 border-t border-slate-200">
                <a
                    href="{{ route('admin.equipments') }}"
                    class="flex-1 text-center px-5 py-3 border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50"
                >
                    Annuler
                </a>
                <button
                    type="submit"
                    class="flex-1 px-5 py-3 bg-amber-500 text-white rounded-lg text-sm font-semibold hover:bg-amber-600 transition-colors"
                >
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
