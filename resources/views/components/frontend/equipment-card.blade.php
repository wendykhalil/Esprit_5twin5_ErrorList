@props(['equipment'])

<div class="bg-white rounded-2xl shadow-sm border border-green-100 overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col">
    <div class="relative">
        <img
            src="{{ $equipment['image'] }}"
            alt="{{ $equipment['name'] }}"
            class="w-full h-48 object-cover bg-green-50"
        />
        <span class="absolute top-3 left-3 bg-white text-green-700 text-xs font-medium px-2.5 py-1 rounded-full border border-green-100 shadow-sm">
            {{ $equipment['category'] }}
        </span>
        @if($equipment['available'])
            <span class="absolute top-3 right-3 bg-green-500 text-white text-xs font-medium px-2.5 py-1 rounded-full">
                Disponible
            </span>
        @else
            <span class="absolute top-3 right-3 bg-gray-400 text-white text-xs font-medium px-2.5 py-1 rounded-full">
                Indisponible
            </span>
        @endif
    </div>
    <div class="p-4 flex flex-col flex-1">
        <h3 class="font-semibold text-green-900 leading-snug mb-1" style="font-family: Fraunces, Georgia, serif">
            {{ $equipment['name'] }}
        </h3>
        <p class="text-sm text-gray-500 leading-relaxed mb-3 flex-1">{{ $equipment['description'] }}</p>

        <div class="flex items-center gap-1 text-sm text-gray-500 mb-3">
            <svg class="w-4 h-4 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {{ $equipment['location'] }}
        </div>

        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-1">
                <div class="w-7 h-7 rounded-full bg-green-100 text-green-700 text-xs font-bold flex items-center justify-center">
                    {{ $equipment['ownerInitial'] }}
                </div>
                <span class="text-xs text-gray-500">{{ $equipment['owner'] }}</span>
            </div>
            <div class="flex items-center gap-1">
                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span class="text-sm font-medium text-gray-700">{{ $equipment['rating'] }}</span>
                <span class="text-xs text-gray-400">({{ $equipment['reviews'] }})</span>
            </div>
        </div>

        <div class="flex flex-col gap-3 pt-3 border-t border-green-50">
            <div>
                <span class="text-lg font-bold text-green-700">{{ $equipment['price'] }} TND</span>
                <span class="text-xs text-gray-500"> / {{ $equipment['period'] }}</span>
            </div>
            <a
                href="{{ route('equipments.show', $equipment['id']) }}"
                class="px-6 py-3 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors text-center whitespace-nowrap"
            >
                Voir les détails
            </a>
        </div>
    </div>
</div>
