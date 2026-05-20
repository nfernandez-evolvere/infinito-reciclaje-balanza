<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('organizacion')?->id;

        return [
            'nombre' => ['required', 'string', 'max:150'],
            'slug'   => ['nullable', 'string', 'max:100', 'alpha_dash', Rule::unique('organizaciones', 'slug')->ignore($id)],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre',
            'slug'   => 'slug',
        ];
    }
}
