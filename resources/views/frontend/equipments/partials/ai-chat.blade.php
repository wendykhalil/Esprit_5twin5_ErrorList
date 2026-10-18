
<section class="bg-white rounded-2xl border border-green-200 shadow-sm overflow-hidden" id="solarshare-ai-chat">
    <div class="bg-green-800 text-white px-5 py-4">
        <h2 class="text-lg font-bold">🤖 SolarShare AI Assistant</h2>
        <p class="text-sm text-green-100 mt-1">
            Posez vos questions sur cet équipement.
        </p>
    </div>

    <div id="ai-chat-messages"
         class="h-80 overflow-y-auto p-5 space-y-4 bg-green-50/40"
         aria-live="polite">
        <div class="bg-white border border-green-100 rounded-xl p-4 text-sm text-slate-700">
            Bonjour ! Je peux vous aider à comprendre cet équipement,
            son utilisation et ses précautions de sécurité.
        </div>
    </div>

    <form id="ai-chat-form" class="p-4 border-t border-green-100">
        <div class="flex gap-2">
            <input id="ai-chat-question"
                   type="text"
                   maxlength="1000"
                   required
                   autocomplete="off"
                   placeholder="Posez votre question..."
                   class="flex-1 min-w-0 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-green-400">

            <button id="ai-chat-submit"
                    type="submit"
                    class="bg-green-700 hover:bg-green-800 text-white font-semibold px-5 rounded-xl disabled:opacity-50">
                Envoyer
            </button>
        </div>

        <p class="text-xs text-slate-500 mt-2">
            Les réponses IA sont indicatives. Consultez toujours le manuel du fabricant.
        </p>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('ai-chat-form');
    const input = document.getElementById('ai-chat-question');
    const submit = document.getElementById('ai-chat-submit');
    const messages = document.getElementById('ai-chat-messages');

    if (!form || !input || !submit || !messages) return;

    const history = [];

    const endpoint = @json(route('equipments.ai.chat', $equipment));

    function addMessage(content, sender) {
        const wrapper = document.createElement('div');

        wrapper.className = sender === 'user'
            ? 'flex justify-end'
            : 'flex justify-start';

        const bubble = document.createElement('div');

        bubble.className = sender === 'user'
            ? 'max-w-[85%] bg-green-700 text-white rounded-xl px-4 py-3 text-sm whitespace-pre-wrap'
            : 'max-w-[85%] bg-white border border-green-100 text-slate-700 rounded-xl px-4 py-3 text-sm whitespace-pre-wrap';

        // textContent prevents HTML injection from AI responses.
        bubble.textContent = content;

        wrapper.appendChild(bubble);
        messages.appendChild(wrapper);
        messages.scrollTop = messages.scrollHeight;

        return wrapper;
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const question = input.value.trim();
        if (!question) return;

        const previousHistory = history.slice(-6);

        addMessage(question, 'user');
        input.value = '';
        submit.disabled = true;
        submit.textContent = 'Patientez...';

        const loading = addMessage(
            'SolarShare AI réfléchit...',
            'assistant'
        );

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': @json(csrf_token())
                },
                body: JSON.stringify({
                    question: question,
                    history: previousHistory
                })
            });

            const data = await response.json();

            loading.remove();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message || 'Impossible de contacter l’assistant.'
                );
            }

            addMessage(data.answer, 'assistant');

            history.push(
                { role: 'user', content: question },
                { role: 'assistant', content: data.answer }
            );

            // Limit conversation history.
            if (history.length > 6) {
                history.splice(0, history.length - 6);
            }

        } catch (error) {
            loading.remove();
            addMessage(
                error.message || 'Une erreur est survenue.',
                'assistant'
            );
        } finally {
            submit.disabled = false;
            submit.textContent = 'Envoyer';
            input.focus();
        }
    });
});
</script>
