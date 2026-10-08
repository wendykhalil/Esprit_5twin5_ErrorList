@extends('layouts.frontend')

@section('title', 'Nouveau ticket - SolarShare')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('support.tickets.index') }}" class="text-sm text-green-700 hover:text-green-800">← Retour à mes tickets</a>
        <h1 class="text-2xl font-bold text-green-900 mt-3" style="font-family: Fraunces, Georgia, serif">Ouvrir un ticket</h1>

        <form method="POST" action="{{ route('support.tickets.store') }}" class="mt-6 bg-white border border-green-100 rounded-xl p-6 space-y-5 shadow-sm">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sujet</label>
                <input type="text" name="subject" value="{{ old('subject') }}" maxlength="150" required
                       class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500" />
                @error('subject')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Priorité</label>
                <select name="priority" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                    <option value="normal" @selected(old('priority', 'normal') === 'normal')>Normale</option>
                    <option value="low" @selected(old('priority') === 'low')>Basse</option>
                    <option value="high" @selected(old('priority') === 'high')>Haute</option>
                </select>
                @error('priority')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                <textarea name="message" rows="6" required class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">{{ old('message') }}</textarea>
                @error('message')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                Envoyer le ticket
            </button>
        </form>
    </div>
</div>
@endsection
