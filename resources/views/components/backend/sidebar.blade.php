@php
    $navItems = [
        ['url' => route('admin.dashboard'), 'label' => 'Tableau de bord', 'route' => 'admin.dashboard'],
        ['url' => route('admin.equipments'), 'label' => 'Équipements', 'route' => 'admin.equipments'],
        ['url' => route('admin.users'), 'label' => 'Utilisateurs', 'route' => 'admin.users'],
        ['url' => route('admin.rentals'), 'label' => 'Locations', 'route' => 'admin.rentals'],
        ['url' => route('admin.reservations.index'), 'label' => 'Réservations', 'route' => 'admin.reservations.*'],
        ['url' => route('admin.deliveries.index'), 'label' => 'Livraisons', 'route' => 'admin.deliveries.*'],
        ['url' => route('admin.payments.index'), 'label' => 'Paiements', 'route' => 'admin.payments.index'],
        ['url' => route('admin.transactions.index'), 'label' => 'Transactions', 'route' => 'admin.transactions.index'],
        ['url' => route('admin.tickets.index'), 'label' => 'Support', 'route' => 'admin.tickets.index'],
        ['url' => route('admin.service-providers.index'), 'label' => 'Prestataires', 'route' => 'admin.service-providers.*'],
    ];
    
    $bottomItems = [
        ['url' => route('admin.settings'), 'label' => 'Paramètres', 'route' => 'admin.settings'],
    ];
@endphp

<aside class="hidden lg:flex fixed top-0 left-0 h-screen w-64 bg-slate-900 flex-col z-30">
    {{-- Logo / Brand --}}
    <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-700/60">
        <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
            </svg>
        </div>
        <div>
            <span class="font-bold text-white text-lg leading-none" style="font-family: Outfit, sans-serif">SolarShare</span>
            <span class="block text-xs text-slate-400 mt-0.5" style="font-family: Outfit, sans-serif">Administration</span>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <p class="px-3 mb-2 text-xs font-600 text-slate-500 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Menu principal</p>
        @foreach($navItems as $item)
            <a
                href="{{ $item['url'] }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-500 transition-all duration-150 {{ 
                    request()->routeIs($item['route']) 
                        ? 'bg-amber-500 text-white shadow-sm' 
                        : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100'
                }}"
                style="font-family: Outfit, sans-serif"
            >
                @if($item['label'] === 'Tableau de bord')
                    <svg class="w-5 h-5 {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                @elseif($item['label'] === 'Équipements')
                    <svg class="w-5 h-5 {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                @elseif($item['label'] === 'Utilisateurs')
                    <svg class="w-5 h-5 {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                @elseif($item['label'] === 'Locations')
                    <svg class="w-5 h-5 {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                    </svg>
                @elseif($item['label'] === 'Réservations')
                    <svg class="w-5 h-5 {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25m10.5-2.25v2.25m3.75 13.5V18a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 18v2.25m19.5 0A2.25 2.25 0 0 1 19.5 21H4.5a2.25 2.25 0 0 1-2.25-2.25V9a2.25 2.25 0 0 1 2.25-2.25h15a2.25 2.25 0 0 1 2.25 2.25v9z" />
                    </svg>
                @elseif($item['label'] === 'Livraisons')
                    <svg class="w-5 h-5 {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                @elseif($item['label'] === 'Paiements')
                    <svg class="w-5 h-5 {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h.008v.008H5.75v-.008zm0 2h.008v.008H5.75V16zm0 2h.008v.008H5.75v-.008zm5-8h.008v.008H10.75v-.008zm0 2h.008v.008H10.75V16zm0 2h.008v.008H10.75v-.008zm5-8h.008v.008H15.75v-.008zm0 2h.008v.008H15.75V16zm0 2h.008v.008H15.75v-.008zm8-5.25c.085 0 .169.002.252.006.486.049.952.27 1.302.461l-1.432 1.901H19.5a.75.75 0 00-.75.75v7.5a.75.75 0 00.75.75h3a.75.75 0 00.75-.75v-6.75l1.432 1.901c-.35.191-.816.412-1.302.461-.083.004-.167.006-.252.006H3.75a2.25 2.25 0 01-2.25-2.25v-10.5a2.25 2.25 0 012.25-2.25h16.5a2.25 2.25 0 012.25 2.25v3.75" />
                    </svg>
                @elseif($item['label'] === 'Transactions')
                    <svg class="w-5 h-5 {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 6.75V3m0 3.75h9m0-3.75v3.75m0 0H21M3.75 6.75h4.5m0 0h-1.5m1.5 0v10.5a1.5 1.5 0 01-1.5 1.5H2.25a1.5 1.5 0 01-1.5-1.5v-10.5m15 0h4.5m0 0h-1.5m1.5 0v10.5a1.5 1.5 0 01-1.5 1.5H12.75a1.5 1.5 0 01-1.5-1.5v-10.5" />
                    </svg>
                @elseif($item['label'] === 'Support')
                    <svg class="w-5 h-5 {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.36 5.47.323.31.52.74.52 1.189v1.303c0 .198.156.364.354.372 1.488.064 2.875-.327 4.093-1.042.193-.113.418-.13.621-.047 1.25.513 2.628.795 4.052.795z" />
                    </svg>
                @elseif($item['label'] === 'Prestataires')
                    <svg class="w-5 h-5 {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />
                    </svg>
                @endif
                {{ $item['label'] }}
                @if(request()->routeIs($item['route']))
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-white/70"></span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Bottom section --}}
    <div class="px-3 py-4 border-t border-slate-700/60 space-y-1">
        @foreach($bottomItems as $item)
            <a
                href="{{ $item['url'] }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-500 transition-all duration-150 {{ 
                    request()->routeIs($item['route']) 
                        ? 'bg-amber-500 text-white' 
                        : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100'
                }}"
                style="font-family: Outfit, sans-serif"
            >
                <svg class="w-5 h-5 {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ $item['label'] }}
            </a>
        @endforeach

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button
                type="submit"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-500 text-slate-400 hover:bg-red-500/10 hover:text-red-400 transition-all duration-150"
                style="font-family: Outfit, sans-serif"
            >
                <svg class="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                </svg>
                Déconnexion
            </button>
        </form>

        {{-- Admin profile --}}
        <div class="flex items-center gap-3 px-3 py-3 mt-2 rounded-lg bg-slate-800">
            <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white text-sm font-700 shrink-0" style="font-family: Outfit, sans-serif">
                A
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-600 text-slate-100" style="font-family: Outfit, sans-serif">Administrateur</p>
                <p class="text-xs text-slate-500" style="font-family: Outfit, sans-serif">admin@solarshare.tn</p>
            </div>
        </div>
    </div>
</aside>
