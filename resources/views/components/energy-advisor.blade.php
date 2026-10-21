@auth
<div x-data="energyAdvisor()" x-cloak class="fixed bottom-5 right-5 z-[1100]">
    <button x-show="!open" @click="open = true" type="button" class="rounded-full bg-green-800 px-5 py-3 font-semibold text-white shadow-xl shadow-green-950/20 hover:bg-green-900" aria-label="Ouvrir le conseiller énergie">
        Conseiller énergie
    </button>
    <section x-show="open" x-transition class="w-[min(23rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-green-100 bg-white shadow-2xl" aria-label="Conseiller énergie">
        <header class="flex items-center justify-between bg-green-900 px-4 py-3 text-white">
            <div><p class="font-semibold">Conseiller énergie</p><p class="text-xs text-green-200">Une aide indicative pour choisir</p></div>
            <button @click="open = false" type="button" class="rounded p-1 text-xl" aria-label="Fermer">×</button>
        </header>
        <div class="max-h-80 space-y-3 overflow-y-auto p-4" aria-live="polite">
            <div class="rounded-xl bg-green-50 p-3 text-sm text-green-950">Bonjour. Quel équipement recherchez-vous ?</div>
            <template x-for="message in messages" :key="message.id">
                <div :class="message.role === 'user' ? 'ml-5 bg-amber-50 text-amber-950' : 'bg-green-50 text-green-950'" class="rounded-xl p-3 text-sm">
                    <template x-if="message.type === 'text'"><p x-text="message.text"></p></template>
                    <template x-if="message.type === 'recommendations'">
                        <div class="space-y-2"><template x-for="item in message.items" :key="item.equipment_id"><article class="rounded-lg border border-green-100 bg-white p-3"><p class="font-semibold" x-text="item.name"></p><p class="mt-1 text-xs" x-text="item.justification"></p><p class="mt-1 text-xs font-semibold text-green-700" x-text="item.kwh_label"></p><a class="mt-2 inline-block text-xs font-bold text-green-800 underline" :href="'/equipements/' + item.equipment_id">Voir l’équipement</a></article></template></div>
                    </template>
                </div>
            </template>
            <div x-show="loading" class="flex gap-1 p-2" aria-label="Analyse en cours"><i class="h-2 w-2 animate-bounce rounded-full bg-green-700"></i><i class="h-2 w-2 animate-bounce rounded-full bg-green-700 [animation-delay:150ms]"></i><i class="h-2 w-2 animate-bounce rounded-full bg-green-700 [animation-delay:300ms]"></i></div>
        </div>
        <div class="flex flex-wrap gap-2 border-t border-slate-100 px-4 py-3"><button @click="need = 'Un équipement solaire puissant'" type="button" class="rounded-full bg-green-50 px-3 py-1 text-xs text-green-800">Solaire puissant</button><button @click="need = 'Une solution éolienne'" type="button" class="rounded-full bg-green-50 px-3 py-1 text-xs text-green-800">Éolien</button></div>
        <form @submit.prevent="send" class="flex gap-2 border-t border-slate-100 p-3"><input x-model="need" maxlength="500" required class="min-w-0 flex-1 rounded-lg border-slate-200 text-sm" placeholder="Votre besoin..."><button class="rounded-lg bg-amber-400 px-3 py-2 font-semibold text-green-950">Envoyer</button></form>
    </section>
</div>
<script>
function energyAdvisor() {
    return { open: false, loading: false, need: '', messages: [], id: 0, async send() {
        const need = this.need.trim(); if (!need || this.loading) return;
        this.messages.push({ id: ++this.id, role: 'user', type: 'text', text: need }); this.need = ''; this.loading = true;
        try {
            const response = await fetch('{{ route('energy-advisor.recommend') }}', { method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}, body: JSON.stringify({ besoin: need }) });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Le conseiller est temporairement indisponible.');
            if (data.message) this.messages.push({ id: ++this.id, role: 'assistant', type: 'text', text: data.message });
            else this.messages.push({ id: ++this.id, role: 'assistant', type: 'recommendations', items: data.recommandations });
        } catch (error) { this.messages.push({ id: ++this.id, role: 'assistant', type: 'text', text: error.message || 'Le conseiller IA est indisponible, démarrez Ollama.' }); }
        this.loading = false;
    }};
}
</script>
@endauth
