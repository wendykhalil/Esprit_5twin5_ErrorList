<nav class="sticky top-0 z-50 bg-white border-b border-green-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                    <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5 text-white" stroke="currentColor" stroke-width="2">
                        <path d="M12 3L4 9v12h5v-7h6v7h5V9L12 3z" fill="currentColor" opacity="0.3"/>
                        <circle cx="12" cy="6" r="2" fill="currentColor"/>
                        <path d="M8 13h8M10 16h4" stroke-linecap="round"/>
                    </svg>
                </div>
                <span class="font-bold text-xl text-green-800" style="font-family: Fraunces, Georgia, serif">
                    Solar<span class="text-green-500">Share</span>
                </span>
            </a>

            <!-- Desktop nav -->
            <div class="hidden md:flex items-center gap-1">
                @php
                    $navLinks = [
                        ['route' => 'home', 'label' => 'Accueil'],
                        ['route' => 'equipments.index', 'label' => 'Équipements'],
                        ['route' => 'how-it-works', 'label' => 'Comment ça marche'],
                        ['route' => 'about', 'label' => 'À propos'],
                    ];
                @endphp
                @foreach($navLinks as $link)
                    <a
                        href="{{ route($link['route']) }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ 
                            request()->routeIs($link['route']) 
                                ? 'bg-green-50 text-green-700' 
                                : 'text-gray-600 hover:text-green-700 hover:bg-green-50'
                        }}"
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>

            <!-- Auth buttons -->
            <div class="hidden md:flex items-center gap-3">
                <a
                    href="{{ route('login') }}"
                    class="px-4 py-2 text-sm font-medium text-green-700 hover:text-green-800 transition-colors"
                >
                    Connexion
                </a>
                <a
                    href="{{ route('register') }}"
                    class="px-4 py-2 text-sm font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow-sm"
                >
                    Inscription
                </a>
            </div>

            <!-- Mobile hamburger -->
            <button
                type="button"
                id="mobileMenuBtn"
                class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-green-50 transition-colors"
                aria-label="Menu"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <!-- Mobile menu -->
        <div id="mobileMenu" class="hidden md:hidden py-4 border-t border-green-100 space-y-1">
            @foreach($navLinks as $link)
                <a
                    href="{{ route($link['route']) }}"
                    onclick="document.getElementById('mobileMenu').classList.add('hidden')"
                    class="block px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ 
                        request()->routeIs($link['route']) 
                            ? 'bg-green-50 text-green-700' 
                            : 'text-gray-600 hover:bg-green-50 hover:text-green-700'
                    }}"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
            <div class="pt-3 border-t border-green-100 flex flex-col gap-2">
                <a
                    href="{{ route('login') }}"
                    onclick="document.getElementById('mobileMenu').classList.add('hidden')"
                    class="block px-4 py-2.5 text-sm font-medium text-center text-green-700 border border-green-200 rounded-lg hover:bg-green-50"
                >
                    Connexion
                </a>
                <a
                    href="{{ route('register') }}"
                    onclick="document.getElementById('mobileMenu').classList.add('hidden')"
                    class="block px-4 py-2.5 text-sm font-medium text-center bg-green-600 text-white rounded-lg hover:bg-green-700"
                >
                    Inscription
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
    document.getElementById('mobileMenuBtn').addEventListener('click', function() {
        document.getElementById('mobileMenu').classList.toggle('hidden');
    });
</script>
