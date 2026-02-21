<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use Laravel\Sanctum\Sanctum;

class CompanyTest extends TestCase
{
    // esto limpia la base de datos de prueba despues de cada test
    use RefreshDatabase;

    // probamos que un usuario logueado pueda registrar su empresa por la API
    public function test_usuario_puede_crear_empresa_via_api(): void
    {
        $user = User::factory()->create();
        
        // simulamos que el usuario ya inicio sesion con Sanctum
        Sanctum::actingAs($user);

        $datos = [
            'name' => 'Mi Nueva Pyme',
            'industry' => 'Comercio',
            'country' => 'El Salvador',
            'region' => 'Occidente',
            'keywords' => ['ventas', 'retail']
        ];

        // mandamos la peticion POST
        $res = $this->postJson('/api/companies', $datos);

        // chequeamos que responda "creado" (201) y que los datos coincidan
        $res->assertStatus(201)
            ->assertJsonPath('company.name', 'Mi Nueva Pyme');
            
        // verificamos que de verdad este guardada en la tabla de empresas
        $this->assertDatabaseHas('companies', ['name' => 'Mi Nueva Pyme', 'user_id' => $user->id]);
    }

    // aqui probamos la privacidad: yo solo debo ver mis empresas, no las de otros
    public function test_usuario_solo_ve_sus_propias_empresas(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // le damos una empresa a cada uno
        Company::create([
            'name' => 'Empresa de User 1', 'industry' => 'T', 'country' => 'C', 'region' => 'R',
            'user_id' => $user1->id
        ]);

        Company::create([
            'name' => 'Empresa de User 2', 'industry' => 'T', 'country' => 'C', 'region' => 'R',
            'user_id' => $user2->id
        ]);

        // entramos como usuario 1
        Sanctum::actingAs($user1);

        // pedimos la lista de empresas
        $res = $this->getJson('/api/companies');

        // solo debe aparecer la empresa del user 1, la del user 2 debe estar oculta
        $res->assertStatus(200)
            ->assertJsonCount(1, 'companies')
            ->assertJsonPath('companies.0.name', 'Empresa de User 1');
    }

    // otra prueba de seguridad: si intento ver una empresa que no es mia, debe dar error
    public function test_usuario_no_puede_ver_empresa_ajena(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $empresaDeUser2 = Company::create([
            'name' => 'Secreta', 'industry' => 'T', 'country' => 'C', 'region' => 'R',
            'user_id' => $user2->id
        ]);

        // entramos como usuario 1 e intentamos ver el detalle de la empresa del user 2
        Sanctum::actingAs($user1);

        $res = $this->getJson("/api/companies/{$empresaDeUser2->id}");

        // el sistema debe decir que no la encuentra (404) para no dar pistas de que existe
        $res->assertStatus(404);
    }
}
