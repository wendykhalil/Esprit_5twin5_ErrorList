
@php
    $navItems = [
        ['url' => route('admin.dashboard'), 'label' => 'Tableau de bord', 'route' => 'admin.dashboard', 'icon' => 'dashboard'],
        ['url' => url('/admin/equipements'), 'label' => 'Équipements', 'route' => 'admin.equipments', 'icon' => 'equipment'],
        ['url' => route('admin.equipments.analytics'), 'label' => 'Statistiques équipements', 'route' => 'admin.equipments.analytics', 'icon' => 'analytics'],
        ['url' => route('admin.equipment-guides.index'), 'label' => "Guides d'utilisation", 'route' => 'admin.equipment-guides.*', 'icon' => 'guides'],
        ['url' => route('admin.users'), 'label' => 'Utilisateurs', 'route' => 'admin.users', 'icon' => 'users'],
        ['url' => route('admin.rentals'), 'label' => 'Locations', 'route' => 'admin.rentals', 'icon' => 'rentals'],
        ['url' => route('admin.reservations.index'), 'label' => 'Réservations', 'route' => 'admin.reservations.*', 'icon' => 'calendar'],
        ['url' => route('admin.deliveries.index'), 'label' => 'Livraisons', 'route' => 'admin.deliveries.*', 'icon' => 'delivery'],
        ['url' => route('admin.payments.index'), 'label' => 'Paiements', 'route' => 'admin.payments.index', 'icon' => 'payment'],
        ['url' => route('admin.transactions.index'), 'label' => 'Transactions', 'route' => 'admin.transactions.index', 'icon' => 'transactions'],
        ['url' => route('admin.compensation-codes.index'), 'label' => 'Codes de Compensation', 'route' => 'admin.compensation-codes.*', 'icon' => 'check'],
        ['url' => route('admin.tickets.index'), 'label' => 'Support', 'route' => 'admin.tickets.index', 'icon' => 'support'],
        ['url' => route('admin.reclamations.index'), 'label' => 'Réclamations', 'route' => 'admin.reclamations.*', 'icon' => 'support'],
        ['url' => route('admin.service-providers.index'), 'label' => 'Prestataires', 'route' => 'admin.service-providers.*', 'icon' => 'providers'],
    ];

    $bottomItems = [
        ['url' => route('admin.settings'), 'label' => 'Paramètres', 'route' => 'admin.settings'],
    ];
@endphp

<aside class="hidden lg:flex fixed top-0 left-0 h-screen w-64 bg-slate-900 flex-col z-30">

    {{-- SOLARSHARE BRAND --}}
    <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-700/60">
        <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-white" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
            </svg>
        </div>

        <div>
            <span class="font-bold text-white text-lg leading-none"
                  style="font-family: Outfit, sans-serif">
                SolarShare
            </span>
            <span class="block text-xs text-slate-400 mt-0.5"
                  style="font-family: Outfit, sans-serif">
                Administration
            </span>
        </div>
    </div>

    {{-- MAIN NAVIGATION --}}
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

        <p class="px-3 mb-2 text-xs text-slate-500 uppercase tracking-wider"
           style="font-family: Outfit, sans-serif">
            Menu principal
        </p>

        @foreach($navItems as $item)
            @php
                $active = request()->routeIs($item['route']);

                $iconPaths = [
                    'dashboard' => [
                        'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6z',
                        'M13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6z',
                        'M3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25z',
                        'M13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25a2.25 2.25 0 01-2.25-2.25v-2.25z'
                    ],
                    'equipment' => [
                        'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z'
                    ],
                    'analytics' => [
                        'M3 3v18h18',
                        'M7 16v-5',
                        'M12 16V7',
                        'M17 16V4'
                    ],
                    'guides' => [
                        'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5 5.754 5 4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18c1.746 0 3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'
                    ],
                    'users' => [
                        'M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2',
                        'M9 11a4 4 0 100-8 4 4 0 000 8z',
                        'M22 21v-2a4 4 0 00-3-3.87',
                        'M16 3.13a4 4 0 010 7.75'
                    ],
                    'rentals' => [
                        'M8 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V8l-5-5H8z',
                        'M14 3v5h5',
                        'M7 13h10',
                        'M7 17h10'
                    ],
                    'calendar' => [
                        'M8 2v4',
                        'M16 2v4',
                        'M3 10h18',
                        'M5 4h14a2 2 0 012 2v13a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z'
                    ],
                    'delivery' => [
                        'M9 12l2 2 4-4',
                        'M12 22a10 10 0 110-20 10 10 0 010 20z'
                    ],
                    'payment' => [
                        'M2 7h20v10H2z',
                        'M2 11h20',
                        'M6 15h4'
                    ],
                    'transactions' => [
                        'M4 7h16',
                        'M7 4l-3 3 3 3',
                        'M20 17H4',
                        'M17 14l3 3-3 3'
                    ],
                    'check' => [
                        'M9 12l2 2 4-4',
                        'M12 22a10 10 0 110-20 10 10 0 010 20z'
                    ],
                    'support' => [
                        'M21 11.5a8.38 8.38 0 01-.9 3.8A8.5 8.5 0 0112.5 20a8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8A8.5 8.5 0 0112.5 3h.5a8.48 8.48 0 018 8v.5z'
                    ],
                    'providers' => [
                        'M3 7h18v13H3z',
                        'M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2',
                        'M3 12h18'
                    ]
                ];
            @endphp

            <a href="{{ $item['url'] }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
               {{ $active
                   ? 'bg-amber-500 text-white shadow-sm'
                   : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' }}"
               style="font-family: Outfit, sans-serif">

                <svg class="w-5 h-5 shrink-0 {{ $active ? 'text-white' : 'text-slate-500' }}"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="1.75"
                     stroke-linecap="round"
                     stroke-linejoin="round">

                    @foreach($iconPaths[$item['icon']] ?? [] as $path)
                        <path d="{{ $path }}"/>
                    @endforeach
                </svg>

                <span class="flex-1 min-w-0">
                    {{ $item['label'] }}
                </span>

                @if($active)
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-white/70 shrink-0"></span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- BOTTOM NAVIGATION --}}
    <div class="px-3 py-4 border-t border-slate-700/60 space-y-1">

        @foreach($bottomItems as $item)
            @php
                $active = request()->routeIs($item['route']);
            @endphp

            <a href="{{ $item['url'] }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-150
               {{ $active
                   ? 'bg-amber-500 text-white'
                   : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' }}"
               style="font-family: Outfit, sans-serif">

                <svg class="w-5 h-5 shrink-0"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 15.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7z"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06-1.9 1.9-.06-.06A1.65 1.65 0 0016 18.4a1.65 1.65 0 00-1 1.5V20h-6v-.1a1.65 1.65 0 00-1-1.5 1.65 1.65 0 00-1.82.33l-.06.06-1.9-1.9.06-.06A1.65 1.65 0 004.6 15a1.65 1.65 0 00-1.5-1H3v-4h.1a1.65 1.65 0 001.5-1 1.65 1.65 0 00-.33-1.82l-.06-.06 1.9-1.9.06.06A1.65 1.65 0 008 5.6a1.65 1.65 0 001-1.5V4h6v.1a1.65 1.65 0 001 1.5 1.65 1.65 0 001.82-.33l.06-.06 1.9 1.9-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.5 1h.1v4h-.1a1.65 1.65 0 00-1.5 1z"/>
                </svg>

                {{ $item['label'] }}
            </a>
        @endforeach

        {{-- LOGOUT --}}
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf

            <button type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-400 hover:bg-red-500/10 hover:text-red-400 transition-all duration-150"
                    style="font-family: Outfit, sans-serif">

                <svg class="w-5 h-5 text-slate-500"
                     fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                </svg>

                Déconnexion
            </button>
        </form>

        {{-- ADMIN PROFILE --}}
        <div class="flex items-center gap-3 px-3 py-3 mt-2 rounded-lg bg-slate-800">
            <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white text-sm font-bold shrink-0"
                 style="font-family: Outfit, sans-serif">
                A
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-100"
                   style="font-family: Outfit, sans-serif">
                    Administrateur
                </p>

                <p class="text-xs text-slate-500"
                   style="font-family: Outfit, sans-serif">
                    admin@solarshare.tn
                </p>
            </div>
        </div>
    </div>
</aside>
