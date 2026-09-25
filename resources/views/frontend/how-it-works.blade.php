@extends('layouts.frontend')

@section('title', 'Comment ça marche - SolarShare')

@section('content')

    {{-- Header --}}
    <div class="bg-gradient-to-br from-green-700 to-green-900 py-20 text-center px-4">
        <h1 class="text-4xl sm:text-5xl font-bold text-white mb-4" style="font-family: Fraunces, Georgia, serif">
            Comment ça marche ?
        </h1>
        <p class="text-xl text-green-200 max-w-2xl mx-auto">
            Louer ou partager des équipements d'énergie renouvelable n'a jamais été aussi simple.
        </p>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        {{-- For renters --}}
        <div class="mb-20">
            <div class="text-center mb-12">
                <span class="inline-block bg-green-100 text-green-700 text-sm font-semibold px-4 py-1.5 rounded-full mb-3">Je veux louer</span>
                <h2 class="text-3xl font-bold text-green-900" style="font-family: Fraunces, Georgia, serif">
                    Louer un équipement en 4 étapes
                </h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $forRenters = [
                        ['step' => '01', 'icon' => '🔍', 'title' => 'Recherchez', 'desc' => 'Parcourez notre catalogue ou utilisez la recherche pour trouver l\'équipement dont vous avez besoin près de chez vous.'],
                        ['step' => '02', 'icon' => '📋', 'title' => 'Consultez les détails', 'desc' => 'Vérifiez les caractéristiques, les photos, les avis des locataires précédents et les informations du propriétaire.'],
                        ['step' => '03', 'icon' => '📅', 'title' => 'Réservez', 'desc' => 'Sélectionnez vos dates de location, confirmez votre réservation et effectuez le paiement sécurisé en ligne.'],
                        ['step' => '04', 'icon' => '♻️', 'title' => 'Utilisez & retournez', 'desc' => 'Récupérez l\'équipement auprès du propriétaire, utilisez-le et retournez-le en bon état à la date convenue.'],
                    ];
                @endphp
                @foreach($forRenters as $s)
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4 relative">
                            {{ $s['icon'] }}
                            <span class="absolute -top-2 -right-2 w-6 h-6 bg-green-600 text-white text-xs font-bold rounded-full flex items-center justify-center">
                                {{ $s['step'] }}
                            </span>
                        </div>
                        <h3 class="font-bold text-green-900 mb-2" style="font-family: Fraunces, Georgia, serif">{{ $s['title'] }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">{{ $s['desc'] }}</p>
                    </div>
                @endforeach
            </div>
            <div class="text-center mt-8">
                <a href="{{ route('equipments.index') }}" class="px-8 py-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors shadow-md">
                    Parcourir les équipements
                </a>
            </div>
        </div>

        <div class="border-t border-green-100 my-12" />

        {{-- For owners --}}
        <div class="mb-20">
            <div class="text-center mb-12">
                <span class="inline-block bg-yellow-100 text-yellow-700 text-sm font-semibold px-4 py-1.5 rounded-full mb-3">Je veux partager</span>
                <h2 class="text-3xl font-bold text-green-900" style="font-family: Fraunces, Georgia, serif">
                    Partager et gagner en 4 étapes
                </h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $forOwners = [
                        ['step' => '01', 'icon' => '📝', 'title' => 'Publiez votre annonce', 'desc' => 'Créez une annonce avec des photos et une description détaillée de votre équipement en quelques minutes.'],
                        ['step' => '02', 'icon' => '💰', 'title' => 'Fixez votre prix', 'desc' => 'Définissez votre tarif de location par jour, semaine ou mois. Vous gardez 85% des revenus générés.'],
                        ['step' => '03', 'icon' => '📲', 'title' => 'Gérez vos demandes', 'desc' => 'Recevez des demandes de location, acceptez ou refusez selon vos disponibilités depuis votre tableau de bord.'],
                        ['step' => '04', 'icon' => '💳', 'title' => 'Percevez vos revenus', 'desc' => 'Les paiements sont sécurisés et versés sur votre compte après chaque location confirmée.'],
                    ];
                @endphp
                @foreach($forOwners as $s)
                    <div class="text-center">
                        <div class="w-16 h-16 bg-yellow-50 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4 relative border border-yellow-200">
                            {{ $s['icon'] }}
                            <span class="absolute -top-2 -right-2 w-6 h-6 bg-yellow-400 text-yellow-900 text-xs font-bold rounded-full flex items-center justify-center">
                                {{ $s['step'] }}
                            </span>
                        </div>
                        <h3 class="font-bold text-green-900 mb-2" style="font-family: Fraunces, Georgia, serif">{{ $s['title'] }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">{{ $s['desc'] }}</p>
                    </div>
                @endforeach
            </div>
            <div class="text-center mt-8">
                <a href="{{ route('equipments.create') }}" class="px-8 py-4 bg-yellow-400 hover:bg-yellow-300 text-green-900 font-bold rounded-xl transition-colors shadow-md">
                    Publier mon équipement
                </a>
            </div>
        </div>

        {{-- FAQ --}}
        <div>
            <h2 class="text-3xl font-bold text-green-900 text-center mb-10" style="font-family: Fraunces, Georgia, serif">
                Questions fréquentes
            </h2>
            <div class="space-y-4">
                @php
                    $faqs = [
                        ['q' => 'Quels types d\'équipements puis-je louer sur SolarShare ?', 'a' => 'Vous pouvez louer des panneaux solaires portables, des batteries solaires, des stations électriques portables, des mini-éoliennes et tout autre équipement d\'énergie renouvelable.'],
                        ['q' => 'Comment sont gérés les paiements ?', 'a' => 'Les paiements sont sécurisés et traités par notre plateforme. Le montant est réservé lors de la réservation et versé au propriétaire après confirmation de la remise de l\'équipement.'],
                        ['q' => 'Que se passe-t-il si l\'équipement est endommagé ?', 'a' => 'Notre politique de protection couvre les dommages accidentels. Les locataires sont tenus responsables des dommages causés par négligence. Nous recommandons de documenter l\'état de l\'équipement à la remise et au retour.'],
                        ['q' => 'Combien puis-je gagner en louant mon équipement ?', 'a' => 'Les revenus dépendent de votre équipement, de son prix de location et de sa disponibilité. Vous recevez 85% du montant de chaque location. Nos propriétaires actifs gagnent en moyenne 400-800 TND par mois.'],
                    ];
                @endphp
                @foreach($faqs as $faq)
                    <details class="group bg-green-50 rounded-2xl border border-green-100">
                        <summary class="flex items-center justify-between p-5 cursor-pointer font-semibold text-green-900 list-none">
                            {{ $faq['q'] }}
                            <svg class="w-5 h-5 text-green-500 group-open:rotate-180 transition-transform shrink-0 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="px-5 pb-5 text-sm text-gray-600 leading-relaxed">{{ $faq['a'] }}</div>
                    </details>
                @endforeach
            </div>
        </div>
    </div>

@endsection
