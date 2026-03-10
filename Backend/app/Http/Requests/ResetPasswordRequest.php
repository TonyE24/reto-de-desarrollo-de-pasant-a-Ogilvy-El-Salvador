<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida el cambio de contraseña con token de reset.
 * Issue #32 - Validación Avanzada de Inputs
 */
class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required'       => 'El token de recuperación es obligatorio.',
            'email.required'       => 'El correo electrónico es obligatorio.',
            'email.email'          => 'Ingresa un correo electrónico válido.',
            'password.min'         => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.max'         => 'La contraseña no puede superar los 72 caracteres.',
            'password.confirmed'   => 'Las contraseñas no coinciden.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email ?? '')),
        ]);
    }
}
