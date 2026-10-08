@extends('layouts.frontend')

@section('title', 'Nouveau ticket - SolarShare')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('support.tickets.index') }}" class="text-sm text-green-700 hover:text-green-800">← Retour à mes tickets</a>
        <h1 class="text-2xl font-bold text-green-900 mt-3" style="font-family: Fraunces, Georgia, serif">Ouvrir un ticket</h1>
        <p class="mt-2 text-sm text-gray-600">
            Décrivez votre problème le plus précisément possible. Notre équipe vous répondra par e-mail.
        </p>

        @if ($errors->any())
            <div class="mt-6 bg-red-50 border border-red-200 rounded-xl p-4" role="alert">
                <p class="font-semibold text-red-800 mb-2">Veuillez corriger les champs suivants :</p>
                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            id="supportTicketForm"
            method="POST"
            action="{{ route('support.tickets.store') }}"
            class="mt-6 bg-white border border-green-100 rounded-xl p-6 space-y-5 shadow-sm"
            novalidate
        >
            @csrf

            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
                    Sujet <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="subject"
                    name="subject"
                    value="{{ old('subject') }}"
                    autocomplete="off"
                    placeholder="Ex : Problème sur ma dernière facture"
                    aria-describedby="subject-hint @error('subject') subject-error @enderror"
                    class="w-full rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 @error('subject') border-red-500 @else border-gray-300 @enderror"
                />
                <p id="subject-hint" class="mt-1 text-xs text-gray-500">
                    Entre 5 et 150 caractères. Résumez votre demande en une phrase claire.
                </p>
                @error('subject')
                    <p id="subject-error" class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priorité</label>
                <select
                    id="priority"
                    name="priority"
                    aria-describedby="priority-hint"
                    class="w-full rounded-lg border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                >
                    <option value="normal" @selected(old('priority', 'normal') === 'normal')>Normale — réponse standard</option>
                    <option value="low" @selected(old('priority') === 'low')>Basse — question non urgente</option>
                    <option value="high" @selected(old('priority') === 'high')>Haute — blocage important</option>
                </select>
                <p id="priority-hint" class="mt-1 text-xs text-gray-500">
                    Choisissez « Haute » uniquement si vous ne pouvez plus utiliser le service.
                </p>
                @error('priority')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">
                    Message <span class="text-red-500">*</span>
                </label>
                <textarea
                    id="message"
                    name="message"
                    rows="6"
                    placeholder="Expliquez le contexte, les étapes déjà essayées et ce que vous attendez comme solution…"
                    aria-describedby="message-hint message-count @error('message') message-error @enderror"
                    class="w-full rounded-lg text-sm resize-y focus:outline-none focus:ring-2 focus:ring-green-500 @error('message') border-red-500 @else border-gray-300 @enderror"
                >{{ old('message') }}</textarea>
                <div class="mt-1 flex flex-wrap items-center justify-between gap-2">
                    <p id="message-hint" class="text-xs text-gray-500">
                        Minimum 20 caractères. Incluez dates, références de paiement ou numéro de réservation si utile.
                    </p>
                    <p id="message-count" class="text-xs text-gray-400" aria-live="polite">0 / 5000</p>
                </div>
                @error('message')
                    <p id="message-error" class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                Envoyer le ticket
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const message = document.getElementById('message');
    const countEl = document.getElementById('message-count');

    function updateMessageCount() {
        countEl.textContent = message.value.length + ' / 5000';
    }

    message.addEventListener('input', updateMessageCount);
    updateMessageCount();
});
</script>
@endsection
