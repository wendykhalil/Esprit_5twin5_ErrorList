<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Validator;

trait RejectsUnexpectedFields
{
    /**
     * @param  list<string>  $allowed
     */
    protected function rejectUnexpectedFields(Validator $validator, array $allowed): void
    {
        $validator->after(function (Validator $validator) use ($allowed) {
            $unexpected = array_values(array_diff(array_keys($this->all()), $allowed));

            if ($unexpected === []) {
                return;
            }

            if (in_array('role', $unexpected, true)) {
                $validator->errors()->add('role', 'Le rôle du compte ne peut pas être défini ici.');
            }

            $others = array_values(array_diff($unexpected, ['role']));

            if ($others !== []) {
                $validator->errors()->add('form', 'La requête contient des champs non autorisés.');
            }
        });
    }
}
