<footer class="bg-green-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">
            <!-- Brand -->
            <div class="lg:col-span-2">
                <a href="{{ route('home') }}" class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5 text-white" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="6" r="2" fill="currentColor"/>
                            <path d="M8 13h8M10 16h4" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="font-bold text-xl" style="font-family: Fraunces, Georgia, serif">
                        Solar<span class="text-green-400">Share</span>
                    </span>
                </a>
                <p class="text-green-200 text-sm leading-relaxed max-w-xs">
                    La plateforme peer-to-peer de location d'équipements d'énergie renouvelable entre particuliers en Tunisie.
                </p>
                <div class="flex items-center gap-3 mt-6">
                    @php
                        $socials = ['facebook', 'twitter', 'instagram', 'linkedin'];
                    @endphp
                    @foreach($socials as $social)
                        <a
                            href="#"
                            class="w-9 h-9 rounded-lg bg-green-800 hover:bg-green-700 flex items-center justify-center transition-colors"
                            aria-label="{{ $social }}"
                        >
                            @if($social === 'facebook')
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                                </svg>
                            @elseif($social === 'twitter')
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/>
                                </svg>
                            @elseif($social === 'instagram')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                                    <circle cx="12" cy="12" r="4"/>
                                    <circle cx="17.5" cy="6.5" r="0.5" fill="currentColor"/>
                                </svg>
                            @elseif($social === 'linkedin')
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/>
                                    <circle cx="4" cy="4" r="2"/>
                                </svg>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Navigation -->
            <div>
                <h4 class="font-semibold text-sm uppercase tracking-wider text-green-400 mb-4">Navigation</h4>
                <ul class="space-y-2.5">
                    @php
                        $footerLinks = [
                            ['route' => 'home', 'label' => 'Accueil'],
                            ['route' => 'equipments.index', 'label' => 'Équipements'],
                            ['route' => 'how-it-works', 'label' => 'Comment ça marche'],
                            ['route' => 'about', 'label' => 'À propos'],
                        ];
                    @endphp
                    @foreach($footerLinks as $link)
                        <li>
                            <a href="{{ route($link['route']) }}" class="text-sm text-green-200 hover:text-white transition-colors">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Compte -->
            <div>
                <h4 class="font-semibold text-sm uppercase tracking-wider text-green-400 mb-4">Compte</h4>
                <ul class="space-y-2.5">
                    @php
                        $accountLinks = [
                            ['route' => 'login', 'label' => 'Connexion'],
                            ['route' => 'register', 'label' => 'Inscription'],
                            ['route' => 'profile', 'label' => 'Mon profil'],
                            ['route' => 'rentals.index', 'label' => 'Mes réservations'],
                        ];
                    @endphp
                    @foreach($accountLinks as $link)
                        <li>
                            <a href="{{ route($link['route']) }}" class="text-sm text-green-200 hover:text-white transition-colors">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Support -->
            <div>
                <h4 class="font-semibold text-sm uppercase tracking-wider text-green-400 mb-4">Support</h4>
                <ul class="space-y-2.5">
                    <li>
                        <a href="{{ route('contact') }}" class="text-sm text-green-200 hover:text-white transition-colors">
                            Centre d'aide
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('contact') }}" class="text-sm text-green-200 hover:text-white transition-colors">
                            Contact
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-sm text-green-200 hover:text-white transition-colors">
                            Conditions d'utilisation
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-sm text-green-200 hover:text-white transition-colors">
                            Politique de confidentialité
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-green-800 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-green-400">© 2026 SolarShare. Tous droits réservés.</p>
            <p class="text-sm text-green-500 flex items-center gap-1">
                Fait avec <span class="text-yellow-400">♥</span> pour une énergie plus responsable
            </p>
        </div>
    </div>
</footer>
