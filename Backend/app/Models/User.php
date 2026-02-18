<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     * Esta propiedad nos ayuda a definir que campos se pueden asignar masivamente
     */
    protected $fillable = [
        'name', // 'name' es una propiedad que se puede asignar masivamente
        'email', // 'email' es una propiedad que se puede asignar masivamente
        'password', // 'password' es una propiedad que se puede asignar masivamente
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     * Esta propiedad nos ayuda a ocultar ciertos datos que no queremos que se muestren en la respuesta
     */
    protected $hidden = [
        'password', // 'password' es una propiedad que se oculta por seguridad
        'remember_token', // 'remember_token' es una propiedad que se oculta por seguridad
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     * Esta función nos ayuda a convertir los datos a un formato especifico como por ejemplo la fecha de verificación del correo y la contraseña
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // 'datetime' es una función que convierte la fecha a un formato legible
            'password' => 'hashed', // 'hashed' es una función que convierte la contraseña a un formato ilegible
        ];
    }
}
