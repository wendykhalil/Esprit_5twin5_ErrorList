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
    <details class="group" id="compensationDetails">
        <summary class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700 hover:text-green-700">
            <span class="inline-block h-5 w-5 transition-transform group-open:rotate-180">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </span>
            <span>💳 Appliquer un code de compensation</span>
        </summary>
        <div class="mt-3 rounded-xl border border-slate-200 bg-white px-4 py-3">
            <div class="space-y-3">
                <div>
                    <label for="compensation_code" class="mb-2 block text-sm font-semibold text-slate-700">Code de compensation</label>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <input type="text"
                               name="compensation_code"
                               id="compensation_code"
                               class="flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 font-mono"
                               placeholder="Ex: CC-ABCDEF123456"
                               value="{{ old('compensation_code', $r?->compensationCode?->code) }}"
                               autocomplete="off"
                               @keypress.enter="validateCompensationCode()">
                        <button type="button"
                                class="w-full rounded-xl bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed px-4 py-3 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:w-auto"
                                @click="validateCompensationCode()"
                                id="validateBtn">
                            Valider
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">
                        Entrez un code que vous avez reçu pour appliquer une réduction à votre réservation.
                    </p>
                </div>

                <!-- Validation Messages -->
                <div id="codeValidationMessage" class="hidden rounded-lg border px-3 py-2 text-sm" role="alert"></div>

                <!-- Applied Code Info -->
                @if($r?->compensation_code_id && $r?->compensationCode)
                    @php($code = $r->compensationCode)
                    @php($discountText = $code->type === 'percentage' ? "{$code->value}%" : number_format((float) $code->value, 2, ',', ' ') . ' TND')
                    <div class="mt-3 rounded-lg bg-green-50 border border-green-200 p-3">
                        <p class="text-sm font-semibold text-green-800">✓ Code appliqué : {{ $discountText }}</p>
                        <p class="text-xs text-green-700 mt-2">Économies : <span class="font-semibold">{{ number_format((float) $r->discount_amount, 2, ',', ' ') }} TND</span></p>
                        <button type="button" 
                                class="mt-2 text-sm text-green-700 hover:text-green-900 hover:underline font-semibold"
                                @click="removeCompensationCode()"
                                id="removeCodeBtn">
                            ✕ Retirer le code
                        </button>
                    </div>
                @endif
            </div>
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
    const compensationCodeInput = document.getElementById('compensation_code');
    const validateBtn = document.getElementById('validateBtn');
    const messageDiv = document.getElementById('codeValidationMessage');

    // Get pre-filled days from the query parameter if provided
    const preFilledDays = {{ json_encode($preFilledData['days'] ?? null) }};

    // Store original price for discount calculations
    let basePrice = 0;
    let appliedDiscount = {
        type: null,
        value: 0,
        amount: 0
    };
    let isValidating = false;  // Prevent double-submit

    function updateEstimate() {
        let price = 0;

        if (equipment && equipment.tagName === 'SELECT') {
            // Interactive select mode
            const option = equipment.options[equipment.selectedIndex];
            price = Number(option?.dataset.price || 0);
            dailyPrice.textContent = price ? `${price.toFixed(2)} TND` : '-';
        } else {
            // Read-only mode (equipment pre-filled)
            const priceText = dailyPrice.textContent.match(/[\d,.]+/);
            price = priceText ? parseFloat(priceText[0].replace(',', '.')) : 0;
        }

        if (!price || !startDate.value || !endDate.value) {
            estimatedTotal.textContent = '-';
            basePrice = 0;
            return;
        }

        const days = Math.max(1, Math.ceil((new Date(endDate.value) - new Date(startDate.value)) / 86400000));
        basePrice = days * price;
        
        // Recalculate total with discount if applied
        updateTotalWithDiscount();
    }

    function updateTotalWithDiscount() {
        let total = basePrice;
        let discountDisplay = '';

        if (appliedDiscount.type) {
            if (appliedDiscount.type === 'percentage') {
                appliedDiscount.amount = Math.round(basePrice * appliedDiscount.value / 100 * 100) / 100;
            } else {
                appliedDiscount.amount = Math.min(appliedDiscount.value, basePrice);
            }
            total = Math.max(0, basePrice - appliedDiscount.amount);
            discountDisplay = ` - ${appliedDiscount.amount.toFixed(2)} TND de réduction`;
        }

        const days = !startDate.value || !endDate.value ? 0 : Math.max(1, Math.ceil((new Date(endDate.value) - new Date(startDate.value)) / 86400000));
        
        if (days > 0) {
            estimatedTotal.textContent = `${total.toFixed(2)} TND (${days} jour${days > 1 ? 's' : ''})${discountDisplay}`;
        } else {
            estimatedTotal.textContent = '-';
        }
    }

    // Validate compensation code via AJAX
    window.validateCompensationCode = async function() {
        const code = compensationCodeInput.value.trim();
        messageDiv.classList.add('hidden');
        messageDiv.innerHTML = '';

        if (!code) {
            showValidationMessage('Veuillez entrer un code de compensation', 'error');
            appliedDiscount = { type: null, value: 0, amount: 0 };
            updateTotalWithDiscount();
            return;
        }

        // Prevent double-submit during validation
        if (isValidating) {
            return;
        }
        isValidating = true;

        validateBtn.disabled = true;
        validateBtn.textContent = 'Validation en cours...';

        try {
            const response = await fetch('{{ route("compensation-codes.validate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ code })
            });

            const data = await response.json();

            if (response.ok) {
                // Code is valid
                appliedDiscount = {
                    type: data.discount_type,
                    value: data.discount_value,
                    amount: 0
                };
                updateTotalWithDiscount();

                const discountText = data.discount_type === 'percentage' 
                    ? `${data.discount_value}% de réduction`
                    : `${data.discount_value.toFixed(2)} TND de réduction`;

                showValidationMessage(
                    `✓ ${data.message} ${discountText} (Valide jusqu'au ${data.valid_until})`,
                    'success'
                );
            } else {
                // Code is invalid
                appliedDiscount = { type: null, value: 0, amount: 0 };
                updateTotalWithDiscount();
                showValidationMessage(`✕ ${data.message}`, 'error');
            }
        } catch (error) {
            appliedDiscount = { type: null, value: 0, amount: 0 };
            updateTotalWithDiscount();
            showValidationMessage('Erreur lors de la validation du code. Veuillez réessayer.', 'error');
            console.error('Validation error:', error);
        } finally {
            isValidating = false;
            validateBtn.disabled = false;
            validateBtn.textContent = 'Valider';
        }
    };

    // Remove compensation code
    window.removeCompensationCode = function() {
        compensationCodeInput.value = '';
        appliedDiscount = { type: null, value: 0, amount: 0 };
        messageDiv.classList.add('hidden');
        messageDiv.innerHTML = '';
        updateTotalWithDiscount();
    };

    function showValidationMessage(message, type) {
        messageDiv.classList.remove('hidden');
        messageDiv.innerHTML = message;
        
        if (type === 'success') {
            messageDiv.className = 'rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800';
        } else {
            messageDiv.className = 'rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800';
        }
    }

    // Event listeners
    if (equipment && equipment.tagName === 'SELECT') {
        equipment.addEventListener('change', updateEstimate);
    }
    if (startDate) startDate.addEventListener('change', updateEstimate);
    if (endDate) endDate.addEventListener('change', updateEstimate);
    if (compensationCodeInput) {
        compensationCodeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                validateCompensationCode();
            }
        });
    }

    // If days are pre-filled and a start date exists, calculate the end date
    if (preFilledDays && startDate.value) {
        const days = Math.max(1, parseInt(preFilledDays, 10));
        const start = new Date(startDate.value);
        const end = new Date(start);
        end.setDate(end.getDate() + days);
        endDate.value = end.toISOString().split('T')[0];
    }

    updateEstimate();
});
</script>
