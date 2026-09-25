@extends('layouts.frontend')

@section('title', 'Contactez-nous - SolarShare')

@section('content')

    <div class="min-h-screen bg-gray-50">
        {{-- Header --}}
        <div class="bg-white border-b border-green-100 py-12 text-center">
            <h1 class="text-4xl font-bold text-green-900 mb-2" style="font-family: Fraunces, Georgia, serif">
                Contactez-nous
            </h1>
            <p class="text-gray-500 max-w-xl mx-auto">
                Une question ? Un problème ? Notre équipe est disponible pour vous aider.
            </p>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid lg:grid-cols-3 gap-8">
                {{-- Info cards --}}
                <div class="space-y-4">
                    @php
                        $contacts = [
                            ['icon' => '📧', 'title' => 'Email', 'info' => 'contact@solarshare.tn', 'sub' => 'Réponse sous 24h'],
                            ['icon' => '📞', 'title' => 'Téléphone', 'info' => '+216 71 123 456', 'sub' => 'Lun–Ven, 9h–18h'],
                            ['icon' => '📍', 'title' => 'Adresse', 'info' => 'Avenue Habib Bourguiba', 'sub' => 'Tunis 1000, Tunisie'],
                        ];
                    @endphp
                    @foreach($contacts as $c)
                        <div class="bg-white rounded-2xl border border-green-100 p-5 shadow-sm flex gap-4 items-start">
                            <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center text-xl shrink-0">{{ $c['icon'] }}</div>
                            <div>
                                <p class="font-semibold text-green-900 text-sm">{{ $c['title'] }}</p>
                                <p class="text-sm text-gray-800">{{ $c['info'] }}</p>
                                <p class="text-xs text-gray-400">{{ $c['sub'] }}</p>
                            </div>
                        </div>
                    @endforeach

                    <div class="bg-green-50 rounded-2xl border border-green-200 p-5">
                        <h3 class="font-bold text-green-900 mb-3 text-sm">Foire aux questions</h3>
                        <div class="space-y-2">
                            @php
                                $faqLinks = [
                                    'Comment fonctionne la réservation ?',
                                    'Que faire si l\'équipement est endommagé ?',
                                    'Comment recevoir mes paiements ?',
                                ];
                            @endphp
                            @foreach($faqLinks as $q)
                                <a href="{{ route('how-it-works') }}" class="block text-xs text-green-700 hover:text-green-900 hover:underline">→ {{ $q }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Form --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-6 sm:p-8">
                        <h2 class="font-bold text-green-900 text-xl mb-6">Envoyer un message</h2>
                        <form class="space-y-4" id="contactForm">
                            @csrf
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nom complet *</label>
                                    <input
                                        type="text"
                                        name="name"
                                        required
                                        placeholder="Votre nom"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                                    <input
                                        type="email"
                                        name="email"
                                        required
                                        placeholder="vous@exemple.com"
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Sujet *</label>
                                <select
                                    name="subject"
                                    required
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-green-400 bg-white"
                                >
                                    <option value="">Sélectionner un sujet</option>
                                    <option>Question générale</option>
                                    <option>Problème avec une réservation</option>
                                    <option>Signalement d'un équipement</option>
                                    <option>Problème de paiement</option>
                                    <option>Partenariat</option>
                                    <option>Autre</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Message *</label>
                                <textarea
                                    name="message"
                                    required
                                    rows="6"
                                    placeholder="Décrivez votre demande en détail..."
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-400 resize-none"
                                ></textarea>
                            </div>
                            <button
                                type="submit"
                                class="w-full py-3.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors"
                            >
                                Envoyer le message
                            </button>
                        </form>

                        <div id="successMessage" style="display: none;" class="text-center py-12">
                            <div class="text-6xl mb-4">✅</div>
                            <h2 class="text-2xl font-bold text-green-900 mb-2" style="font-family: Fraunces, Georgia, serif">
                                Message envoyé !
                            </h2>
                            <p class="text-gray-500 mb-6">Merci pour votre message. Notre équipe vous répondra dans les plus brefs délais.</p>
                            <button
                                type="button"
                                onclick="window.location.reload()"
                                class="px-6 py-3 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 transition-colors"
                            >
                                Envoyer un autre message
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('contactForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            // Simulate form submission
            document.getElementById('contactForm').style.display = 'none';
            document.getElementById('successMessage').style.display = 'block';
        });
    </script>

@endsection
