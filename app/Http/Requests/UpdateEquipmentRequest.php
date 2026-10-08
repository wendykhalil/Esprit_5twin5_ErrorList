<?php

namespace App\Http\Requests;

class UpdateEquipmentRequest extends StoreEquipmentRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $equipment = $this->route('equipment');

        return $equipment !== null && (int) $equipment->user_id === (int) $user->id;
    }
}
