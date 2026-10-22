
@php
    $iconPaths = [
        'dashboard' => [
            'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6z',
            'M13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6z',
            'M3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25z',
            'M13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25a2.25 2.25 0 01-2.25-2.25v-2.25z',
        ],
        'equipment' => [
            'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z',
        ],
        'analytics' => [
            'M3 3v18h18',
            'M7 16v-5',
            'M12 16V7',
            'M17 16V4',
        ],
        'guides' => [
            'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5 5.754 5 4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18c1.746 0 3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        ],
        'users' => [
            'M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2',
            'M9 11a4 4 0 100-8 4 4 0 000 8z',
            'M22 21v-2a4 4 0 00-3-3.87',
            'M16 3.13a4 4 0 010 7.75',
        ],
        'rentals' => [
            'M8 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V8l-5-5H8z',
            'M14 3v5h5',
            'M7 13h10',
            'M7 17h10',
        ],
        'calendar' => [
            'M8 2v4',
            'M16 2v4',
            'M3 10h18',
            'M5 4h14a2 2 0 012 2v13a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z',
        ],
        'delivery' => [
            'M9 12l2 2 4-4',
            'M12 22a10 10 0 110-20 10 10 0 010 20z',
        ],
        'payment' => [
            'M2 7h20v10H2z',
            'M2 11h20',
            'M6 15h4',
        ],
        'transactions' => [
            'M4 7h16',
            'M7 4l-3 3 3 3',
            'M20 17H4',
            'M17 14l3 3-3 3',
        ],
        'check' => [
            'M9 12l2 2 4-4',
            'M12 22a10 10 0 110-20 10 10 0 010 20z',
        ],
        'support' => [
            'M21 11.5a8.38 8.38 0 01-.9 3.8A8.5 8.5 0 0112.5 20a8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8A8.5 8.5 0 0112.5 3h.5a8.48 8.48 0 018 8v.5z',
        ],
        'providers' => [
            'M3 7h18v13H3z',
            'M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2',
            'M3 12h18',
        ],
        'tools' => [
            'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.05a4.5 4.5 0 004.486-6.32l-3.276 3.277a1.5 1.5 0 01-2.122-2.122l3.277-3.276a4.5 4.5 0 00-6.32 4.486c.138.58.114 1.193-.05 1.743',
        ],
    ];

    $groups = [
        [
            'label' => 'Pilotage',
            'items' => [
                ['url' => route('admin.dashboard'), 'label' => 'Tableau de bord', 'route' => 'admin.dashboard', 'icon' => 'dashboard'],
                ['url' => route('admin.reports.index'), 'label' => 'Rapports IA', 'route' => 'admin.reports.*', 'icon' => 'analytics'],
                ['url' => route('admin.impact.index'), 'label' => 'Impact écologique', 'route' => 'admin.impact.*', 'icon' => 'analytics'],
            ],
        ],
        [
            'label' => 'Catalogue',
            'items' => [
                ['url' => url('/admin/equipements'), 'label' => 'Équipements', 'route' => 'admin.equipments', 'icon' => 'equipment'],
                ['url' => route('admin.equipments.analytics'), 'label' => 'Statistiques équipements', 'route' => 'admin.equipments.analytics', 'icon' => 'analytics'],
                ['url' => route('admin.equipment-guides.index'), 'label' => "Guides d'utilisation", 'route' => 'admin.equipment-guides.*', 'icon' => 'guides'],
            ],
        ],
        [
            'label' => 'Activité',
            'items' => [
                ['url' => route('admin.users'), 'label' => 'Utilisateurs', 'route' => 'admin.users', 'icon' => 'users'],
                ['url' => route('admin.rentals'), 'label' => 'Locations', 'route' => 'admin.rentals', 'icon' => 'rentals'],
                ['url' => route('admin.reservations.index'), 'label' => 'Réservations', 'route' => 'admin.reservations.*', 'icon' => 'calendar'],
                ['url' => route('admin.deliveries.index'), 'label' => 'Livraisons', 'route' => 'admin.deliveries.*', 'icon' => 'delivery'],
            ],
        ],
        [
            'label' => 'Finance',
            'items' => [
                ['url' => route('admin.payments.index'), 'label' => 'Paiements', 'route' => 'admin.payments.index', 'icon' => 'payment'],
                ['url' => route('admin.transactions.index'), 'label' => 'Transactions', 'route' => 'admin.transactions.index', 'icon' => 'transactions'],
                ['url' => route('admin.compensation-codes.index'), 'label' => 'Codes de Compensation', 'route' => 'admin.compensation-codes.*', 'icon' => 'check'],
            ],
        ],
        [
            'label' => 'Support',
            'items' => [
                ['url' => route('admin.tickets.index'), 'label' => 'Support', 'route' => 'admin.tickets.index', 'icon' => 'support'],
                ['url' => route('admin.reclamations.index'), 'label' => 'Incidents', 'route' => 'admin.reclamations.*', 'icon' => 'support'],
            ],
        ],
        [
            'label' => 'Services',
            'items' => [
                ['url' => route('admin.service-providers.index'), 'label' => 'Prestataires', 'route' => 'admin.service-providers.*', 'icon' => 'providers'],
                ['url' => route('admin.service-requests.index'), 'label' => 'Interventions', 'route' => 'admin.service-requests.*', 'icon' => 'tools'],
            ],
        ],
        [
            'label' => 'Récompenses',
            'items' => [
                ['url' => route('admin.badges.index'), 'label' => 'Badges', 'route' => 'admin.badges.*', 'icon' => 'check'],
            ],
        ],
    ];

    $settingsActive = request()->routeIs('admin.settings');
@endphp

<aside id="admin-sidebar" class="admin-sidebar fixed top-0 left-0 z-30 flex h-dvh w-64 flex-col overflow-hidden text-slate-100">

    <div class="flex shrink-0 items-center gap-3 border-b border-white/10 px-5 py-4">
        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500">
            <svg class="h-5 w-5 text-slate-950" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <span class="block truncate text-base font-bold leading-none text-white" style="font-family: Outfit, sans-serif">SolarShare</span>
            <span class="mt-1 block text-xs text-slate-300" style="font-family: Outfit, sans-serif">Administration</span>
        </div>
    </div>

    <nav class="admin-sidebar-nav min-h-0 flex-1 overflow-y-auto overscroll-y-contain px-3 py-3" aria-label="Navigation administration">
        @foreach($groups as $group)
            <div class="mb-3 last:mb-0">
                <p class="px-3 pb-1 pt-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400" style="font-family: Outfit, sans-serif">
                    {{ $group['label'] }}
                </p>
                <div class="space-y-0.5">
                    @foreach($group['items'] as $item)
                        @php $active = request()->routeIs($item['route']); @endphp
                        <a href="{{ $item['url'] }}"
                           @if($active) aria-current="page" @endif
                           class="flex min-h-10 items-center gap-3 rounded-lg px-3 py-2 text-sm leading-snug focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-300 {{ $active ? 'bg-amber-500 font-semibold text-slate-950' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                           style="font-family: Outfit, sans-serif">
                            <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-slate-950' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                @foreach($iconPaths[$item['icon']] ?? [] as $path)
                                    <path d="{{ $path }}"/>
                                @endforeach
                            </svg>
                            <span class="min-w-0 flex-1">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    <div class="shrink-0 space-y-1 border-t border-white/10 px-3 py-3">
        <a href="{{ route('admin.settings') }}"
           @if($settingsActive) aria-current="page" @endif
           class="flex min-h-10 items-center gap-3 rounded-lg px-3 py-2 text-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-300 {{ $settingsActive ? 'bg-amber-500 font-semibold text-slate-950' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
           style="font-family: Outfit, sans-serif">
            <svg class="h-5 w-5 shrink-0 {{ $settingsActive ? 'text-slate-950' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06-1.9 1.9-.06-.06A1.65 1.65 0 0016 18.4a1.65 1.65 0 00-1 1.5V20h-6v-.1a1.65 1.65 0 00-1-1.5 1.65 1.65 0 00-1.82.33l-.06.06-1.9-1.9.06-.06A1.65 1.65 0 004.6 15a1.65 1.65 0 00-1.5-1H3v-4h.1a1.65 1.65 0 001.5-1 1.65 1.65 0 00-.33-1.82l-.06-.06 1.9-1.9.06.06A1.65 1.65 0 008 5.6a1.65 1.65 0 001-1.5V4h6v.1a1.65 1.65 0 001 1.5 1.65 1.65 0 001.82-.33l.06-.06 1.9 1.9-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.5 1h.1v4h-.1a1.65 1.65 0 00-1.5 1z"/>
            </svg>
            Paramètres
        </a>

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit"
                    class="flex min-h-10 w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm text-slate-300 hover:bg-red-500/15 hover:text-red-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-300"
                    style="font-family: Outfit, sans-serif">
                <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                </svg>
                Déconnexion
            </button>
        </form>

        <div class="mt-1 flex items-center gap-3 rounded-lg bg-white/5 px-3 py-2.5">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-500 text-sm font-bold text-slate-950" style="font-family: Outfit, sans-serif">A</div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-slate-100" style="font-family: Outfit, sans-serif">Administrateur</p>
                <p class="truncate text-xs text-slate-400" style="font-family: Outfit, sans-serif">admin@solarshare.tn</p>
            </div>
        </div>
    </div>
</aside>

<script>
    (function () {
        const sidebar = document.getElementById('admin-sidebar');
        if (!sidebar) {
            return;
        }

        const mobile = window.matchMedia('(max-width: 1023px)');
        const button = document.getElementById('mobileMenuBtn');

        const sync = function () {
            const closed = mobile.matches && !sidebar.classList.contains('is-open');
            sidebar.toggleAttribute('inert', closed);
        };

        sync();
        new MutationObserver(sync).observe(sidebar, { attributes: true, attributeFilter: ['class'] });
        mobile.addEventListener('change', function () {
            if (!mobile.matches) {
                sidebar.classList.remove('is-open');
                const backdrop = document.getElementById('admin-sidebar-backdrop');
                if (backdrop) {
                    backdrop.classList.add('hidden');
                }
                if (button) {
                    button.setAttribute('aria-expanded', 'false');
                }
            }
            sync();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape' || !sidebar.classList.contains('is-open')) {
                return;
            }
            sidebar.classList.remove('is-open');
            const backdrop = document.getElementById('admin-sidebar-backdrop');
            if (backdrop) {
                backdrop.classList.add('hidden');
            }
            if (button) {
                button.setAttribute('aria-expanded', 'false');
                button.focus();
            }
        });
    })();
</script>
