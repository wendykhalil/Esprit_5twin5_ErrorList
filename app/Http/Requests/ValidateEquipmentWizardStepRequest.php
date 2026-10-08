<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidateEquipmentWizardStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $step = (int) $this->input('wizard_step');

        return array_merge(
            [
                'wizard_step' => ['required', 'integer', Rule::in([1, 2, 3, 4])],
            ],
            StoreEquipmentRequest::rulesForWizardStep($step),
        );
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return (new StoreEquipmentRequest())->messages();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return (new StoreEquipmentRequest())->attributes();
    }
}
