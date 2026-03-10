<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida la actualización de datos de una empresa existente.
 * Todos los campos son opcionales (solo se validan si vienen).
 * Issue #32 - Validación Avanzada de Inputs
 */
class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['sometimes', 'string', 'max:255'],
            'industry'   => ['sometimes', 'string', 'in:Tecnología,Alimentos,Comercio,Construcción,Servicios'],
            'country'    => ['sometimes', 'string', 'max:100'],
            'region'     => ['sometimes', 'string', 'max:100'],
            'keywords'   => ['sometimes', 'array', 'max:20'],
            'keywords.*' => ['string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max'        => 'El nombre no puede superar los 255 caracteres.',
            'industry.in'     => 'La industria seleccionada no es válida.',
            'country.max'     => 'El país no puede superar los 100 caracteres.',
            'region.max'      => 'La región no puede superar los 100 caracteres.',
            'keywords.max'    => 'No puedes agregar más de 20 keywords.',
            'keywords.*.max'  => 'Cada keyword no puede superar los 50 caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $sanitized = [];

        if ($this->has('name'))    $sanitized['name']    = strip_tags(trim($this->name));
        if ($this->has('country')) $sanitized['country'] = strip_tags(trim($this->country));
        if ($this->has('region'))  $sanitized['region']  = strip_tags(trim($this->region));
        if ($this->has('keywords')) {
            $sanitized['keywords'] = collect($this->keywords)
                ->map(fn($k) => strip_tags(trim($k)))
                ->filter()
                ->values()
                ->toArray();
        }

        $this->merge($sanitized);
    }
}
