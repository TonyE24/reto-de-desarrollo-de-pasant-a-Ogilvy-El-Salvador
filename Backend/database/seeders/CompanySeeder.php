<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // creamos un usuario de prueba si no existe
        $user = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Tester',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // agregamos una empresa de ejemplo
        Company::create([
            'name' => 'Tecnología Avanzada S.A.',
            'industry' => 'Tecnología',
            'country' => 'El Salvador',
            'region' => 'San Salvador',
            'keywords' => ['software', 'cloud', 'ai'],
            'user_id' => $user->id,
        ]);

        Company::create([
            'name' => 'Café del Valle',
            'industry' => 'Alimentos',
            'country' => 'El Salvador',
            'region' => 'Santa Ana',
            'keywords' => ['café', 'exportación', 'orgánico'],
            'user_id' => $user->id,
        ]);
    }
}
