<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida los datos de registro de un nuevo usuario.
 * Issue #32 - Validación Avanzada de Inputs
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // cualquiera puede registrarse
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-\']+$/u'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'El nombre es obligatorio.',
            'name.regex'       => 'El nombre solo puede contener letras y espacios.',
            'email.required'   => 'El correo electrónico es obligatorio.',
            'email.email'      => 'Ingresa un correo electrónico válido.',
            'email.unique'     => 'Este correo ya está registrado.',
            'password.min'     => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max'     => 'La contraseña no puede superar los 72 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }

    /**
     * Sanitiza los inputs antes de la validación para prevenir XSS
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'  => strip_tags(trim($this->name ?? '')),
            'email' => strtolower(trim($this->email ?? '')),
        ]);
    }
}
