<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida la creación de una nueva empresa.
 * Issue #32 - Validación Avanzada de Inputs
 */
class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ya está protegida por auth:sanctum en las rutas
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'industry' => ['required', 'string', 'in:Tecnología,Alimentos,Comercio,Construcción,Servicios'],
            'country'  => ['required', 'string', 'max:100'],
            'region'   => ['required', 'string', 'max:100'],
            'keywords' => ['nullable', 'array', 'max:20'],
            'keywords.*' => ['string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'El nombre de la empresa es obligatorio.',
            'name.max'           => 'El nombre no puede superar los 255 caracteres.',
            'industry.required'  => 'La industria es obligatoria.',
            'industry.in'        => 'La industria seleccionada no es válida.',
            'country.required'   => 'El país es obligatorio.',
            'region.required'    => 'La región o departamento es obligatorio.',
            'keywords.max'       => 'No puedes agregar más de 20 keywords.',
            'keywords.*.max'     => 'Cada keyword no puede superar los 50 caracteres.',
        ];
    }

    /**
     * Sanitiza los inputs para prevenir XSS
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'    => strip_tags(trim($this->name ?? '')),
            'country' => strip_tags(trim($this->country ?? '')),
            'region'  => strip_tags(trim($this->region ?? '')),
            // sanitizamos cada keyword del array
            'keywords' => collect($this->keywords ?? [])->map(
                fn($k) => strip_tags(trim($k))
            )->filter()->values()->toArray(),
        ]);
    }
}
