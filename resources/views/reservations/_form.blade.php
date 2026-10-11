@php($r = $reservation ?? null)
@php($preFilledData = $preFilledData ?? [])
@php($isEquipmentPreFilled = !empty($preFilledData['equipment_id']))
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
