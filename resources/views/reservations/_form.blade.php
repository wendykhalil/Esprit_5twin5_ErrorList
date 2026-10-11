@php
    $r = $reservation ?? null;
    $preFilledData = $preFilledData ?? [];
    $isEquipmentPreFilled = !empty($preFilledData['equipment_id']);
@endphp
<div class="grid gap-6 sm:grid-cols-2">
<div>
    <label for="equipment_id" class="mb-2 block text-sm font-semibold text-slate-700">Équipement</label>
    @if($isEquipmentPreFilled)
        {{-- Read-only display when coming from equipment detail page --}}
        <div class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm">
            @php($selectedEquipment = $equipments->firstWhere('id', $preFilledData['equipment_id']))
            {{ $selectedEquipment?->name ?? 'Équipement #' . $preFilledData['equipment_id'] }}
        </div>
        <input type="hidden" name="equipment_id" value="{{ $preFilledData['equipment_id'] }}">
        @error('equipment_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        <p id="dailyPrice" class="mt-2 text-sm text-slate-500">Prix par jour : <span class="font-semibold text-slate-700">{{ $selectedEquipment?->price_per_day ? number_format((float) $selectedEquipment->price_per_day, 2) . ' TND' : '-' }}</span></p>
    @else
        {{-- Interactive select when accessing from reservations page --}}
        <select name="equipment_id" id="equipment_id" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500">
            <option value="">Choisir un équipement</option>
            @foreach ($equipments as $equipment)
                <option value="{{ $equipment->id }}" data-price="{{ $equipment->price_per_day ?? 0 }}" @selected(old('equipment_id', $r?->equipment_id) == $equipment->id)>
                    {{ $equipment->name ?? 'Équipement #'.$equipment->id }}
                </option>
            @endforeach
        </select>
        @error('equipment_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        <p id="dailyPrice" class="mt-2 text-sm text-slate-500">Prix par jour : <span class="font-semibold text-slate-700">-</span></p>
    @endif
</div>

<div>
    <label for="date_debut" class="mb-2 block text-sm font-semibold text-slate-700">Date de début</label>
    <input type="date" name="date_debut" id="date_debut" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500"
           value="{{ old('date_debut', $preFilledData['start_date'] ?? $r?->date_debut?->format('Y-m-d')) }}">
    @error('date_debut') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="date_fin" class="mb-2 block text-sm font-semibold text-slate-700">Date de fin</label>
    <input type="date" name="date_fin" id="date_fin" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500"
           value="{{ old('date_fin', $r?->date_fin?->format('Y-m-d')) }}">
    @error('date_fin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="sm:col-span-2 rounded-xl bg-green-50 px-4 py-4">
    <p class="text-sm text-green-800">Total estimé : <span id="estimatedTotal" class="font-bold">-</span></p>
    <p class="mt-1 text-xs text-green-700">Le montant final sera calculé automatiquement lors de la réservation.</p>
</div>

{{-- Compensation Code Section --}}
<div class="sm:col-span-2">
    <details class="group">
        <summary class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700 hover:text-green-700">
            <span class="inline-block h-5 w-5 transition-transform group-open:rotate-180">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </span>
            <span>💳 Appliquer un code de compensation</span>
        </summary>
        <div class="mt-3 rounded-xl border border-slate-200 bg-white px-4 py-3">
            <label for="compensation_code" class="mb-2 block text-sm font-semibold text-slate-700">Code de compensation</label>
            <input type="text"
                   name="compensation_code"
                   id="compensation_code"
                   class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-green-500 focus:ring-green-500 font-mono"
                   placeholder="Ex: CC-ABCDEF123456"
                   value="{{ old('compensation_code', $r?->compensationCode?->code) }}"
                   autocomplete="off">
            @error('compensation_code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs text-slate-500">
                Entrez un code que vous avez reçu pour appliquer une réduction à votre réservation.
            </p>

            {{-- Show current discount if code is applied --}}
            @if($r?->compensation_code_id && $r?->compensationCode)
                @php($code = $r->compensationCode)
                @php($discountText = $code->type === 'percentage' ? "{$code->value}% de réduction" : number_format((float) $code->value, 2, ',', ' ') . ' TND')
                <div class="mt-3 rounded-lg bg-green-50 border border-green-200 p-3">
                    <p class="text-sm font-semibold text-green-800">✓ Code appliqué</p>
                    <p class="text-sm text-green-700 mt-1">{{ $discountText }}</p>
                    <p class="text-xs text-green-600 mt-1">Économies: {{ number_format((float) $r->discount_amount, 2, ',', ' ') }} TND</p>
                </div>
            @endif
        </div>
    </details>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const equipment = document.getElementById('equipment_id');
    const startDate = document.getElementById('date_debut');
    const endDate = document.getElementById('date_fin');
    const dailyPrice = document.querySelector('#dailyPrice span');
    const estimatedTotal = document.getElementById('estimatedTotal');

    // Get pre-filled days from the query parameter if provided
    const preFilledDays = {{ json_encode($preFilledData['days'] ?? null) }};

    function updateEstimate() {
        let price = 0;

        if (equipment && equipment.tagName === 'SELECT') {
            // Interactive select mode
            const option = equipment.options[equipment.selectedIndex];
            price = Number(option?.dataset.price || 0);
            dailyPrice.textContent = price ? `${price.toFixed(2)} TND` : '-';
        } else {
            // Read-only mode (equipment pre-filled)
            // Price is already displayed, just extract from hidden input or data attribute
            const equipmentId = document.querySelector('input[name="equipment_id"]');
            if (equipmentId && equipment) {
                // Get price from the displayed text
                const priceText = dailyPrice.textContent.match(/[\d,.]+/);
                price = priceText ? parseFloat(priceText[0].replace(',', '.')) : 0;
            }
        }

        if (!price || !startDate.value || !endDate.value) {
            estimatedTotal.textContent = '-';
            return;
        }

        const days = Math.max(1, Math.ceil((new Date(endDate.value) - new Date(startDate.value)) / 86400000));
        estimatedTotal.textContent = `${(days * price).toFixed(2)} TND (${days} jour${days > 1 ? 's' : ''})`;
    }

    // If days are pre-filled and a start date exists, calculate the end date
    if (preFilledDays && startDate.value) {
        const days = Math.max(1, parseInt(preFilledDays, 10));
        const start = new Date(startDate.value);
        const end = new Date(start);
        end.setDate(end.getDate() + days);
        endDate.value = end.toISOString().split('T')[0];
    }

    // Only add event listeners to interactive elements
    if (equipment && equipment.tagName === 'SELECT') {
        equipment.addEventListener('change', updateEstimate);
    }

    if (startDate) startDate.addEventListener('change', updateEstimate);
    if (endDate) endDate.addEventListener('change', updateEstimate);

    updateEstimate();
});
</script>
