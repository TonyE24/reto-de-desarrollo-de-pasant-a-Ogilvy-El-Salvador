<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregamos el campo "role" a la tabla users
     * 
     * Este campo define si el usuario es "admin" o "user"
     * Por defecto todos son "user" cuando se registran luego otro admin decide si lo hace admin o no 
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Agregamos el campo role después del campo password
            // 'enum' significa que solo puede tener los valores que definimos en este caso usuario o administrador
            // 'default' significa que si no se especifica será 'user' de tipo usuarios por defecto y after es para que se coloque después del campo password
            $table->enum('role', ['admin', 'user'])->default('user')->after('password');
        });
    }

    /**
     * Revertir la migración en caso que queramos deshacer los cambios realizados
     * 
     * Si ejecutamos "migrate:rollback" esto elimina el campo role de la tabla de usuarios
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
