@extends('layouts.frontend')
@section('title', 'Conseiller énergie - SolarShare')
@section('content')
<div class="mx-auto max-w-4xl px-4 py-12">
    <div class="rounded-3xl bg-green-950 p-8 text-white">
        <p class="text-sm uppercase tracking-widest text-green-300">SolarShare IA</p>
        <h1 class="mt-2 text-3xl font-bold">Trouvez l’équipement adapté</h1>
        @if(!$disponible)<p class="mt-4 rounded-lg bg-amber-100 p-3 text-amber-900">Le conseiller IA est indisponible, démarrez Ollama.</p>@endif
        <form id="advisor-form" class="mt-6 space-y-3">
            @csrf
            <textarea name="besoin" maxlength="500" required placeholder="Exemple : je cherche un équipement solaire puissant pour une journée..." class="w-full rounded-xl border-0 p-4 text-slate-900"></textarea>
            <div class="flex flex-wrap gap-2 text-sm"><button type="button" class="example rounded-full bg-white/15 px-3 py-1" data-value="Un équipement solaire pour produire beaucoup d'énergie">Solaire puissant</button><button type="button" class="example rounded-full bg-white/15 px-3 py-1" data-value="Une solution éolienne économique">Éolien économique</button></div>
            <button @disabled(!$disponible) class="rounded-xl bg-amber-400 px-5 py-3 font-bold text-green-950">Conseiller</button>
        </form>
        <p id="loading" class="mt-4 hidden animate-pulse">Analyse en cours...</p>
        <div id="results" class="mt-6 grid gap-4 sm:grid-cols-2"></div>
    </div>
</div>
<script>
const form=document.getElementById('advisor-form'), results=document.getElementById('results'), loading=document.getElementById('loading');
document.querySelectorAll('.example').forEach(b=>b.onclick=()=>form.besoin.value=b.dataset.value);
form.onsubmit=async e=>{e.preventDefault(); loading.classList.remove('hidden'); results.replaceChildren(); const r=await fetch('{{ route('energy-advisor.recommend') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':form.querySelector('[name=_token]').value},body:JSON.stringify({besoin:form.besoin.value)}); const data=await r.json(); loading.classList.add('hidden'); if(data.message){const p=document.createElement('p');p.className='rounded-lg bg-white/10 p-4';p.textContent=data.message;results.append(p);return;} data.recommandations.forEach(x=>{const card=document.createElement('article');card.className='rounded-xl bg-white p-5 text-slate-900';const title=document.createElement('h2');title.className='font-bold';title.textContent='Équipement #'+x.equipment_id;const text=document.createElement('p');text.className='mt-2 text-sm';text.textContent=x.justification;const link=document.createElement('a');link.className='mt-4 inline-block font-semibold text-green-700';link.href='/equipements/'+x.equipment_id;link.textContent='Réserver';card.append(title,text,link);results.append(card);});};
</script>
@endsection
