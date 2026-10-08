@php
    $pageTitles = [
        'admin.dashboard' => 'Tableau de bord',
        'admin.equipments' => 'Équipements',
        'admin.users' => 'Utilisateurs',
        'admin.rentals' => 'Locations',
        'admin.settings' => 'Paramètres',
    ];
    
    $title = $pageTitles[request()->route()?->getName()] ?? 'Administration';
@endphp

<header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 lg:px-6 flex-shrink-0">
    {{-- Left: hamburger + title --}}
    <div class="flex items-center gap-3">
        <button
            id="mobileMenuBtn"
            class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors"
            aria-label="Ouvrir le menu"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>
        <div>
            <h1 class="font-bold text-slate-800 text-lg leading-none" style="font-family: Outfit, sans-serif">{{ $title }}</h1>
            <p class="text-xs text-slate-400 mt-0.5 hidden sm:block" style="font-family: Outfit, sans-serif">SolarShare — Panneau d'administration</p>
        </div>
    </div>

    {{-- Right: actions --}}
    <div class="flex items-center gap-2">
        {{-- Notifications --}}
        <div class="relative">
            <button
                id="notifBtn"
                class="relative p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <span class="absolute top-1 right-1 w-4 h-4 bg-amber-500 text-white text-xs font-700 rounded-full flex items-center justify-center leading-none">
                    3
                </span>
            </button>

            <div id="notifPanel" class="absolute right-0 top-12 w-80 bg-white rounded-xl shadow-xl border border-slate-200 z-50 hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <p class="font-bold text-slate-800 text-sm" style="font-family: Outfit, sans-serif">Notifications</p>
                </div>
                <div class="divide-y divide-slate-100 max-h-72 overflow-y-auto">
                    <div class="px-4 py-3 hover:bg-slate-50 cursor-pointer transition-colors">
                        <p class="text-sm text-slate-700" style="font-family: Outfit, sans-serif">Nouvelle location créée par Mariem Trabelsi</p>
                        <p class="text-xs text-slate-400 mt-1" style="font-family: Outfit, sans-serif">Il y a 5 min</p>
                    </div>
                    <div class="px-4 py-3 hover:bg-slate-50 cursor-pointer transition-colors">
                        <p class="text-sm text-slate-700" style="font-family: Outfit, sans-serif">Équipement "Panneau solaire 300W" ajouté</p>
                        <p class="text-xs text-slate-400 mt-1" style="font-family: Outfit, sans-serif">Il y a 1h</p>
                    </div>
                    <div class="px-4 py-3 hover:bg-slate-50 cursor-pointer transition-colors">
                        <p class="text-sm text-slate-700" style="font-family: Outfit, sans-serif">Nouvel utilisateur inscrit : Youssef Ben Ali</p>
                        <p class="text-xs text-slate-400 mt-1" style="font-family: Outfit, sans-serif">Il y a 3h</p>
                    </div>
                </div>
                <div class="px-4 py-3 border-t border-slate-100">
                    <button class="text-sm text-amber-600 font-600 hover:text-amber-700 transition-colors" style="font-family: Outfit, sans-serif">
                        Voir toutes les notifications
                    </button>
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="w-px h-6 bg-slate-200 mx-1 hidden sm:block"></div>

        {{-- Admin profile dropdown --}}
        <div class="relative">
            <button
                id="profileBtn"
                class="flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-lg hover:bg-slate-100 transition-colors"
            >
                <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white text-sm font-700 flex-shrink-0" style="font-family: Outfit, sans-serif">
                    A
                </div>
                <div class="hidden sm:block text-left">
                    <p class="text-sm font-600 text-slate-700" style="font-family: Outfit, sans-serif">Administrateur</p>
                    <p class="text-xs text-slate-400 mt-0.5" style="font-family: Outfit, sans-serif">Admin</p>
                </div>
                <svg class="w-4 h-4 text-slate-400 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <div id="profilePanel" class="absolute right-0 top-12 w-52 bg-white rounded-xl shadow-xl border border-slate-200 z-50 hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <p class="text-sm font-600 text-slate-800" style="font-family: Outfit, sans-serif">Administrateur</p>
                    <p class="text-xs text-slate-400" style="font-family: Outfit, sans-serif">admin@solarshare.tn</p>
                </div>
                <div class="py-1.5">
                    <button class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors text-left" style="font-family: Outfit, sans-serif">
                        👤 Mon profil
                    </button>
                    <a href="{{ route('admin.settings') }}" class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors text-left" style="font-family: Outfit, sans-serif">
                        ⚙️ Paramètres
                    </a>
                </div>
                <div class="py-1.5 border-t border-slate-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors text-left"
                            style="font-family: Outfit, sans-serif"
                        >
                            🚪 Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    // Mobile menu toggle
    document.getElementById('mobileMenuBtn').addEventListener('click', function() {
        const sidebar = document.querySelector('aside');
        const backdrop = document.querySelector('.lg\\:hidden.bg-slate-900\\/50');
        
        // Create backdrop if it doesn't exist
        if (!backdrop) {
            const newBackdrop = document.createElement('div');
            newBackdrop.className = 'fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-20 lg:hidden';
            document.body.appendChild(newBackdrop);
            newBackdrop.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                newBackdrop.remove();
            });
            sidebar.classList.remove('-translate-x-full');
        }
    });

    // Notification dropdown
    document.getElementById('notifBtn').addEventListener('click', function() {
        const panel = document.getElementById('notifPanel');
        const profilePanel = document.getElementById('profilePanel');
        profilePanel.classList.add('hidden');
        panel.classList.toggle('hidden');
    });

    // Profile dropdown
    document.getElementById('profileBtn').addEventListener('click', function() {
        const panel = document.getElementById('profilePanel');
        const notifPanel = document.getElementById('notifPanel');
        notifPanel.classList.add('hidden');
        panel.classList.toggle('hidden');
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const notifBtn = document.getElementById('notifBtn');
        const profileBtn = document.getElementById('profileBtn');
        const notifPanel = document.getElementById('notifPanel');
        const profilePanel = document.getElementById('profilePanel');

        if (!notifBtn.contains(event.target) && !notifPanel.contains(event.target)) {
            notifPanel.classList.add('hidden');
        }

        if (!profileBtn.contains(event.target) && !profilePanel.contains(event.target)) {
            profilePanel.classList.add('hidden');
        }
    });
</script>
