<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // esto le da al user la capacidad de crear tokens
use Illuminate\Auth\Notifications\ResetPassword; // la notificacion de reset que voy a personalizar

class User extends Authenticatable
{
    // HasApiTokens es de Sanctum, sin esto no funcionaria el createToken()
    use HasApiTokens, HasFactory, Notifiable;

    // campos que se pueden llenar masivamente (por seguridad solo los que definamos aqui)
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // admin o user, por defecto todos entran como user
    ];

    // campos que nunca se muestran en las respuestas JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // conversiones automaticas de tipos de datos
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // laravel auto-hashea la contrasena al guardarla
        ];
    }

    // sobreescribo este metodo para que el link del email apunte a mi frontend
    // sin esto laravel buscaria una ruta web que no existe en APIs y da error
    public function sendPasswordResetNotification($token): void
    {
        // primero le digo a la notificacion cual es la URL del frontend
        ResetPassword::createUrlUsing(function ($notifiable, $token) {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            // el link que llega al email tiene el token y el email del usuario
            return "{$frontendUrl}/reset-password?token={$token}&email={$notifiable->email}";
        });

        // mando la notificacion con el link personalizado
        $this->notify(new ResetPassword($token));
    }
}
