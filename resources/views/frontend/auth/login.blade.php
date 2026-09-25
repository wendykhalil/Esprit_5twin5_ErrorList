@extends('layouts.frontend')

@section('title', 'Connexion - SolarShare')

@section('content')

    <div class="min-h-screen bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl border border-green-100 shadow-lg p-8">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-green-600 rounded-lg mb-4">
                        <svg viewBox="0 0 24 24" fill="none" class="w-6 h-6 text-white" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="6" r="2" fill="currentColor"/>
                            <path d="M8 13h8M10 16h4" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-green-900" style="font-family: Fraunces, Georgia, serif">Connexion</h1>
                    <p class="text-gray-500 mt-2">Accédez à votre compte SolarShare</p>
                </div>

                <form class="space-y-4" action="#" method="POST">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                        <input
                            type="email"
                            name="email"
                            required
                            placeholder="vous@exemple.com"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Mot de passe *</label>
                        <input
                            type="password"
                            name="password"
                            required
                            placeholder="••••••••"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
                        />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="accent-green-600" />
                            <span class="text-sm text-gray-600">Se souvenir de moi</span>
                        </label>
                        <a href="#" class="text-sm text-green-600 hover:text-green-700">Mot de passe oublié ?</a>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors mt-6"
                    >
                        Se connecter
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-center text-sm text-gray-600">
                        Pas encore inscrit ?
                        <a href="{{ route('register') }}" class="text-green-600 hover:text-green-700 font-semibold">Créer un compte</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

@endsection
