@extends('layouts.backend')
@section('title', 'Guides d’utilisation - SolarShare Admin')
@section('content')
<style>
/* Scoped admin management layout; independent of Tailwind breakpoint generation. */
.ss-admin .ss-kpis { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
.ss-admin .ss-filter { display:flex; flex-wrap:wrap; align-items:center; gap:10px; }
.ss-admin .ss-search { flex:1 1 320px; min-width:180px; }
.ss-admin .ss-select { flex:0 1 230px; min-width:180px; }
.ss-admin .ss-filter-button { flex:0 0 auto; min-width:94px; }
.ss-admin .ss-reset-button { flex:0 0 auto; min-width:90px; }
.ss-admin .ss-kpi-card { min-height:108px; }
.ss-admin .ss-kpi-card .ss-kpi-value { font-size:27px; line-height:1.15; }
.ss-admin .ss-header { min-height:125px; }
.ss-admin .ss-header-subtitle { line-height:1.6; }
.ss-admin .ss-table td { vertical-align:middle; }
.ss-admin .ss-table tbody tr { height:70px; }
@media(min-width:900px){ .ss-admin .ss-kpis {grid-template-columns:repeat(4,minmax(0,1fr));} }
@media(max-width:640px){ .ss-admin .ss-kpis {grid-template-columns:repeat(2,minmax(0,1fr));} .ss-admin .ss-search,.ss-admin .ss-select{flex-basis:100%;} .ss-admin .ss-filter-button,.ss-admin .ss-reset-button{flex:1;} }
</style>
<div class="ss-admin space-y-4" style="font-family: Outfit, sans-serif">
    @if(session('success'))
      <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif
<div class="ss-header relative overflow-hidden rounded-2xl bg-slate-900 px-6 py-5 sm:px-8 text-white shadow-md">
  <div class="pointer-events-none absolute -right-12 -top-24 h-72 w-72 rounded-full border border-white/10"></div>
  <div class="pointer-events-none absolute right-10 -bottom-32 h-64 w-64 rounded-full bg-amber-400/10 blur-3xl"></div>
  <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
    <div><p class="mb-2 text-[11px] font-bold uppercase tracking-[0.22em] text-amber-400">SolarShare</p>
      <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Guides d'utilisation</h1>
      <p class="ss-header-subtitle mt-2 text-sm text-slate-300">Organisez les consignes, les niveaux et la publication des guides.</p>
    </div>
    <a href="{{ route('admin.equipment-guides.create') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-3 text-sm font-bold text-slate-950 shadow-sm transition hover:bg-amber-400 focus:outline-none focus:ring-4 focus:ring-amber-400/30">
      <span class="text-lg leading-none">+</span> Créer un guide
    </a>
  </div>
</div>
<div class="ss-kpis">
<div class="ss-kpi-card rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md">
 <div class="flex items-center justify-between"><span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total guides</span><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 text-lg">▤</span></div>
 <div class="ss-kpi-value mt-2 font-extrabold tracking-tight text-slate-900">{{ \App\Models\EquipmentGuide::count() }}</div></div><div class="ss-kpi-card rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md">
 <div class="flex items-center justify-between"><span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Publiés</span><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 text-lg">✓</span></div>
 <div class="ss-kpi-value mt-2 font-extrabold tracking-tight text-slate-900">{{ \App\Models\EquipmentGuide::where('status', 'published')->count() }}</div></div><div class="ss-kpi-card rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md">
 <div class="flex items-center justify-between"><span class="text-xs font-semibold uppercase tracking-wider text-slate-500">En attente</span><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 text-lg">◷</span></div>
 <div class="ss-kpi-value mt-2 font-extrabold tracking-tight text-slate-900">{{ \App\Models\EquipmentGuide::where('status', 'pending')->count() }}</div></div><div class="ss-kpi-card rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md">
 <div class="flex items-center justify-between"><span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Brouillons</span><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-600 text-lg">✎</span></div>
 <div class="ss-kpi-value mt-2 font-extrabold tracking-tight text-slate-900">{{ \App\Models\EquipmentGuide::where('status', 'draft')->count() }}</div></div></div>
<section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
 <div class="border-b border-slate-100 px-5 py-5 sm:px-6"><h2 class="text-base font-extrabold text-slate-900">Bibliothèque des guides</h2><p class="mt-1 text-xs text-slate-500">{{ $guides->total() }} résultat(s) • Contrôlez les contenus publiés et en attente.</p></div>
 <form method="GET" action="{{ route('admin.equipment-guides.index') }}" class="ss-filter border-b border-slate-100 bg-slate-50/60 p-4">
  <div class="ss-search relative">
   <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
   <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un guide ou un équipement..." aria-label="Rechercher des guides" class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-400 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
  </div>
  <select name="status" aria-label="Statut" class="ss-select rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
   <option value="">Tous les statuts</option><option value="draft" @selected(request('status') === 'draft')>Brouillon</option><option value="pending" @selected(request('status') === 'pending')>En attente</option><option value="published" @selected(request('status') === 'published')>Publié</option>
  </select>
  <button type="submit" class="ss-filter-button rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white transition hover:bg-slate-700">Filtrer</button>
  <a href="{{ route('admin.equipment-guides.index') }}" class="ss-reset-button rounded-xl border border-slate-200 bg-white px-4 py-3 text-center text-sm font-semibold text-slate-600 transition hover:bg-slate-100">Effacer</a>
 </form>
 <div class="overflow-x-auto"><table class="ss-table w-full min-w-[950px] text-left text-sm">
  <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-500"><tr><th class="px-6 py-4">Guide</th><th class="px-5 py-4">Équipement</th><th class="px-5 py-4">Contexte</th><th class="px-5 py-4">Difficulté</th><th class="px-5 py-4">Publication</th><th class="px-6 py-4 text-right">Actions</th></tr></thead>
  <tbody class="divide-y divide-slate-100">
   @forelse($guides as $guide)
    <tr class="transition-colors hover:bg-amber-50/30">
     <td class="px-6 py-4"><div class="flex items-center gap-3"><div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-xl text-amber-700" aria-hidden="true">▤</div><div><a href="{{ route('admin.equipment-guides.show', $guide) }}" class="font-bold text-slate-900 hover:text-amber-700">{{ $guide->title }}</a><p class="mt-1 text-xs text-slate-400">Guide #{{ $guide->id }}</p></div></div></td>
     <td class="px-5 py-4 font-medium text-slate-700">{{ $guide->equipment?->name ?? 'Équipement indisponible' }}</td>
     <td class="max-w-xs px-5 py-4 text-slate-500">{{ \Illuminate\Support\Str::limit($guide->usage_context ?: 'Non renseigné', 55) }}</td>
     <td class="px-5 py-4"><span class="inline-flex rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700">@switch($guide->difficulty_level) @case('beginner') Débutant @break @case('intermediate') Intermédiaire @break @case('advanced') Avancé @break @default {{ $guide->difficulty_level }} @endswitch</span></td>
     <td class="px-5 py-4">@if($guide->status === 'published')<span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Publié</span>@elseif($guide->status === 'pending')<span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">En attente</span>@else<span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">Brouillon</span>@endif</td>
     <td class="px-6 py-4"><div class="flex items-center justify-end gap-2">
      <a href="{{ route('admin.equipment-guides.show', $guide) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:border-amber-300 hover:text-amber-700">Voir</a>
      <a href="{{ route('admin.equipment-guides.edit', $guide) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:border-amber-300 hover:text-amber-700">Modifier</a>
      <form method="POST" action="{{ route('admin.equipment-guides.destroy', $guide) }}" onsubmit="return confirm('Supprimer définitivement ce guide ?')">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-red-100 bg-white px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-50">Supprimer</button></form>
     </div></td>
    </tr>
   @empty
    <tr><td colspan="6" class="px-6 py-16 text-center"><div class="text-3xl">▤</div><p class="mt-3 font-bold text-slate-800">Aucun guide trouvé</p><p class="mt-1 text-sm text-slate-500">Créez un guide ou ajustez les filtres.</p><a href="{{ route('admin.equipment-guides.create') }}" class="mt-4 inline-block text-sm font-bold text-amber-700 hover:underline">Créer un guide</a></td></tr>
   @endforelse
  </tbody>
 </table></div>
 @if($guides->hasPages())<div class="border-t border-slate-100 px-5 py-4">{{ $guides->links() }}</div>@endif
</section>
</div>
@endsection
