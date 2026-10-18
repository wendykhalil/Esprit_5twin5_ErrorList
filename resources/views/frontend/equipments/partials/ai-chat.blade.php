
{{-- SolarShare AI Chatbot V2 — Floating Equipment Assistant --}}

<style>
    #solarshare-ai-widget {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 9999;
        font-family: inherit;
    }

    #solarshare-ai-launcher {
        width: 64px;
        height: 64px;
        border: 0;
        border-radius: 22px;
        background: linear-gradient(135deg, #15803d, #065f46);
        color: white;
        cursor: pointer;
        box-shadow: 0 12px 30px rgba(6, 95, 70, .32);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform .2s, box-shadow .2s;
        margin-left: auto;
        position: relative;
    }

    #solarshare-ai-launcher:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 35px rgba(6, 95, 70, .4);
    }

    .ss-ai-launcher-icon {
        font-size: 29px;
    }

    .ss-ai-notification {
        position: absolute;
        right: -3px;
        top: -3px;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background: #86efac;
        border: 3px solid white;
    }

    #solarshare-ai-panel {
        position: absolute;
        bottom: 80px;
        right: 0;
        width: 390px;
        height: min(570px, calc(100dvh - 120px));
        background: #fff;
        border: 1px solid #dcfce7;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 24px 65px rgba(15, 23, 42, .22);
        display: flex;
        flex-direction: column;
        opacity: 0;
        visibility: hidden;
        transform: translateY(14px) scale(.97);
        transform-origin: bottom right;
        transition: opacity .22s, transform .22s, visibility .22s;
    }

    #solarshare-ai-panel.ss-ai-open {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
    }

    .ss-ai-header {
        background: linear-gradient(120deg, #166534, #047857);
        color: white;
        padding: 19px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }

    .ss-ai-avatar {
        width: 45px;
        height: 45px;
        border-radius: 15px;
        background: rgba(255,255,255,.18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .ss-ai-header-info {
        flex: 1;
        min-width: 0;
    }

    .ss-ai-header-title {
        font-size: 16px;
        font-weight: 750;
        line-height: 1.3;
    }

    .ss-ai-header-subtitle {
        font-size: 12px;
        color: #dcfce7;
        margin-top: 4px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .ss-ai-header-actions {
        display: flex;
        gap: 6px;
    }

    .ss-ai-icon-button {
        width: 31px;
        height: 31px;
        background: rgba(255,255,255,.14);
        color: white;
        border: none;
        border-radius: 9px;
        cursor: pointer;
        font-size: 17px;
    }

    .ss-ai-icon-button:hover {
        background: rgba(255,255,255,.25);
    }

    #ai-chat-messages {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        background: #f8faf9;
        padding: 19px 15px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        scroll-behavior: smooth;
    }

    .ss-ai-message {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        max-width: 100%;
    }

    .ss-ai-message.user {
        justify-content: flex-end;
    }

    .ss-ai-bubble {
        max-width: 85%;
        padding: 11px 14px;
        border-radius: 17px;
        font-size: 13px;
        line-height: 1.6;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .ss-ai-message.assistant .ss-ai-bubble {
        background: white;
        color: #334155;
        border: 1px solid #e2e8f0;
        border-bottom-left-radius: 5px;
        box-shadow: 0 2px 6px rgba(0,0,0,.025);
    }

    .ss-ai-message.user .ss-ai-bubble {
        background: #15803d;
        color: white;
        border-bottom-right-radius: 5px;
    }

    .ss-ai-mini-avatar {
        width: 26px;
        height: 26px;
        flex-shrink: 0;
        background: #dcfce7;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .ss-ai-suggestions {
        padding: 12px 15px 5px;
        background: #fff;
        border-top: 1px solid #f1f5f9;
        display: flex;
        gap: 7px;
        overflow-x: auto;
        flex-shrink: 0;
    }

    .ss-ai-suggestion {
        border: 1px solid #bbf7d0;
        background: #f0fdf4;
        color: #166534;
        padding: 8px 11px;
        border-radius: 30px;
        font-size: 11px;
        white-space: nowrap;
        cursor: pointer;
        flex-shrink: 0;
    }

    .ss-ai-suggestion:hover {
        background: #dcfce7;
    }

    .ss-ai-suggestion:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    #ai-chat-form {
        padding: 11px 14px 14px;
        background: white;
        flex-shrink: 0;
    }

    .ss-ai-input-row {
        display: flex;
        gap: 9px;
        align-items: center;
    }

    #ai-chat-question {
        flex: 1;
        min-width: 0;
        height: 44px;
        padding: 0 14px;
        border: 1px solid #e2e8f0;
        border-radius: 13px;
        outline: none;
        font-size: 13px;
        color: #0f172a;
        background: #f8fafc;
    }

    #ai-chat-question:focus {
        border-color: #22c55e;
        box-shadow: 0 0 0 3px rgba(34,197,94,.1);
    }

    #ai-chat-submit {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        border: none;
        border-radius: 13px;
        background: #15803d;
        color: white;
        cursor: pointer;
        font-size: 20px;
    }

    #ai-chat-submit:hover {
        background: #166534;
    }

    #ai-chat-submit:disabled {
        opacity: .55;
        cursor: not-allowed;
    }

    .ss-ai-disclaimer {
        text-align: center;
        font-size: 10px;
        color: #94a3b8;
        margin-top: 9px;
    }

    .ss-ai-typing {
        display: flex;
        gap: 4px;
        align-items: center;
        min-height: 15px;
    }

    .ss-ai-typing span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #16a34a;
        animation: ss-ai-bounce 1.1s infinite ease-in-out;
    }

    .ss-ai-typing span:nth-child(2) {
        animation-delay: .15s;
    }

    .ss-ai-typing span:nth-child(3) {
        animation-delay: .3s;
    }

    @keyframes ss-ai-bounce {
        0%, 60%, 100% { transform: translateY(0); opacity: .5; }
        30% { transform: translateY(-5px); opacity: 1; }
    }

    @media (max-width: 480px) {
        #solarshare-ai-widget {
            right: 12px;
            bottom: 12px;
        }

        #solarshare-ai-launcher {
            width: 56px;
            height: 56px;
            border-radius: 18px;
        }

        #solarshare-ai-panel {
            width: calc(100vw - 24px);
            height: min(570px, calc(100dvh - 90px));
            bottom: 70px;
            border-radius: 20px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #solarshare-ai-panel,
        #solarshare-ai-launcher,
        .ss-ai-typing span {
            animation: none;
            transition: none;
        }
    }
</style>

<div id="solarshare-ai-widget">

    {{-- Floating launcher --}}
    <button
        type="button"
        id="solarshare-ai-launcher"
        aria-label="Ouvrir l'assistant SolarShare AI"
        aria-expanded="false"
        aria-controls="solarshare-ai-panel"
        title="Discuter avec SolarShare AI"
    >
        <span class="ss-ai-launcher-icon" aria-hidden="true">🤖</span>
        <span class="ss-ai-notification" aria-hidden="true"></span>
    </button>

    {{-- Chat window --}}
    <section
        id="solarshare-ai-panel"
        role="dialog"
        aria-label="Assistant SolarShare AI"
        aria-hidden="true"
    >
        <header class="ss-ai-header">
            <div class="ss-ai-avatar" aria-hidden="true">🤖</div>

            <div class="ss-ai-header-info">
                <div class="ss-ai-header-title">SolarShare AI</div>
                <div class="ss-ai-header-subtitle">
                    Assistant pour {{ $equipment->name }}
                </div>
            </div>

            <div class="ss-ai-header-actions">
                <button
                    type="button"
                    id="ai-chat-clear"
                    class="ss-ai-icon-button"
                    title="Effacer la conversation"
                    aria-label="Effacer la conversation"
                >↺</button>

                <button
                    type="button"
                    id="ai-chat-close"
                    class="ss-ai-icon-button"
                    title="Fermer"
                    aria-label="Fermer l'assistant"
                >×</button>
            </div>
        </header>

        <div id="ai-chat-messages" aria-live="polite" aria-relevant="additions">
            <div class="ss-ai-message assistant">
                <span class="ss-ai-mini-avatar" aria-hidden="true">🤖</span>
                <div class="ss-ai-bubble">
                    Bonjour ! 👋 Je suis votre assistant SolarShare AI.

                    Je peux vous aider à comprendre cet équipement, son utilisation et ses précautions de sécurité.

                    Comment puis-je vous aider ?
                </div>
            </div>
        </div>

        <div class="ss-ai-suggestions" id="ai-chat-suggestions">
            <button type="button" class="ss-ai-suggestion"
                    data-question="Comment utiliser cet équipement ?">
                ⚡ Utilisation
            </button>

            <button type="button" class="ss-ai-suggestion"
                    data-question="Quelles sont les précautions de sécurité pour cet équipement ?">
                🛡️ Sécurité
            </button>

            <button type="button" class="ss-ai-suggestion"
                    data-question="Quelles sont les caractéristiques techniques de cet équipement ?">
                🔧 Caractéristiques
            </button>
        </div>

        <form id="ai-chat-form">
            <div class="ss-ai-input-row">
                <input
                    type="text"
                    id="ai-chat-question"
                    placeholder="Posez votre question..."
                    maxlength="1000"
                    autocomplete="off"
                    aria-label="Votre question"
                    required
                >

                <button type="submit" id="ai-chat-submit"
                        aria-label="Envoyer le message"
                        title="Envoyer">
                    ➤
                </button>
            </div>

            <p class="ss-ai-disclaimer">
                ✨ Réponses générées par IA · Vérifiez le manuel du fabricant
            </p>
        </form>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const launcher = document.getElementById('solarshare-ai-launcher');
    const panel = document.getElementById('solarshare-ai-panel');
    const closeButton = document.getElementById('ai-chat-close');
    const clearButton = document.getElementById('ai-chat-clear');
    const form = document.getElementById('ai-chat-form');
    const input = document.getElementById('ai-chat-question');
    const submit = document.getElementById('ai-chat-submit');
    const messages = document.getElementById('ai-chat-messages');
    const suggestions = document.getElementById('ai-chat-suggestions');

    if (!launcher || !panel || !form || !input ||
        !submit || !messages || !suggestions) return;

    const endpoint = @json(route('equipments.ai.chat', $equipment));
    const csrfToken = @json(csrf_token());

    const history = [];
    const initialMessages = messages.innerHTML;

    let busy = false;

    function setOpen(open) {
        panel.classList.toggle('ss-ai-open', open);
        panel.setAttribute('aria-hidden', String(!open));
        launcher.setAttribute('aria-expanded', String(open));
        launcher.setAttribute(
            'aria-label',
            open ? "Fermer l'assistant SolarShare AI" : "Ouvrir l'assistant SolarShare AI"
        );

        if (open) {
            input.focus();
            messages.scrollTop = messages.scrollHeight;
        } else {
            launcher.focus();
        }
    }

    launcher.addEventListener('click', function () {
        setOpen(!panel.classList.contains('ss-ai-open'));
    });

    closeButton.addEventListener('click', function () {
        setOpen(false);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' &&
            panel.classList.contains('ss-ai-open')) {
            setOpen(false);
        }
    });

    function addMessage(content, sender, isTyping = false) {
        const wrapper = document.createElement('div');
        wrapper.className = 'ss-ai-message ' + sender;

        if (sender === 'assistant') {
            const avatar = document.createElement('span');
            avatar.className = 'ss-ai-mini-avatar';
            avatar.textContent = '🤖';
            avatar.setAttribute('aria-hidden', 'true');
            wrapper.appendChild(avatar);
        }

        const bubble = document.createElement('div');
        bubble.className = 'ss-ai-bubble';

        if (isTyping) {
            const typing = document.createElement('div');
            typing.className = 'ss-ai-typing';
            typing.setAttribute('aria-label', 'Réponse en cours');

            for (let i = 0; i < 3; i++) {
                typing.appendChild(document.createElement('span'));
            }

            bubble.appendChild(typing);
        } else {
            // textContent prevents HTML injection from user or AI responses.
            bubble.textContent = content;
        }

        wrapper.appendChild(bubble);
        messages.appendChild(wrapper);
        messages.scrollTop = messages.scrollHeight;

        return wrapper;
    }

    function setBusy(value) {
        busy = value;
        submit.disabled = value;
        input.disabled = value;

        suggestions.querySelectorAll('button').forEach(function (button) {
            button.disabled = value;
        });
    }

    async function sendQuestion(question) {
        question = question.trim();

        if (!question || busy) return;

        const previousHistory = history.slice(-6);

        addMessage(question, 'user');
        input.value = '';
        setBusy(true);

        const loading = addMessage('', 'assistant', true);

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    question: question,
                    history: previousHistory
                })
            });

            const contentType = response.headers.get('content-type') || '';

            const data = contentType.includes('application/json')
                ? await response.json()
                : {};

            loading.remove();

            if (response.status === 401) {
                throw new Error('Connectez-vous pour utiliser cet assistant.');
            }

            if (response.status === 419) {
                throw new Error('Session expirée. Actualisez la page.');
            }

            if (response.status === 429) {
                throw new Error('Trop de questions. Réessayez dans une minute.');
            }

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message || "L'assistant est temporairement indisponible."
                );
            }

            addMessage(data.answer, 'assistant');

            history.push(
                { role: 'user', content: question },
                { role: 'assistant', content: data.answer }
            );

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
            setBusy(false);

            if (panel.classList.contains('ss-ai-open')) {
                input.focus();
            }
        }
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        sendQuestion(input.value);
    });

    suggestions.querySelectorAll('[data-question]').forEach(function (button) {
        button.addEventListener('click', function () {
            sendQuestion(button.dataset.question);
        });
    });

    clearButton.addEventListener('click', function () {
        if (busy) return;

        history.length = 0;
        messages.innerHTML = initialMessages;
        input.value = '';
        input.focus();
    });
});
</script>
