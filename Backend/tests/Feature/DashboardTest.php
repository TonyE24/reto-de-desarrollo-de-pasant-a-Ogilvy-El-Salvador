<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\PredictionIntelligence;
use App\Models\InnovationIntelligence;
use Laravel\Sanctum\Sanctum;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    // datos basicos de una empresa de prueba
    private function createCompanyForUser(User $user): Company
    {
        return Company::create([
            'name'     => 'Test Corp',
            'industry' => 'Tecnología',
            'country'  => 'El Salvador',
            'region'   => 'San Salvador',
            'user_id'  => $user->id,
        ]);
    }

    /**
     * Verifica que el endpoint de dashboard devuelva los 4 KPIs correctamente
     */
    public function test_dashboard_devuelve_los_cuatro_kpis(): void
    {
        $user    = User::factory()->create();
        $company = $this->createCompanyForUser($user);

        Sanctum::actingAs($user);

        $res = $this->getJson("/api/dashboard?company_id={$company->id}");

        $res->assertStatus(200)
            ->assertJsonStructure([
                'company_name',
                'kpis' => [
                    'market_share'         => ['value', 'label', 'trend'],
                    'sentiment'            => ['value', 'label', 'status'],
                    'next_prediction'      => ['value', 'label'],
                    'active_opportunities' => ['value', 'label'],
                ],
            ]);
    }

    /**
     * Verifica que el filtro de fechas se aplica a los datos de prediccion
     */
    public function test_filtro_por_fecha_funciona(): void
    {
        $user    = User::factory()->create();
        $company = $this->createCompanyForUser($user);

        // insertamos datos historicos en distintas fechas
        PredictionIntelligence::create(['company_id' => $company->id, 'date' => '2025-01-01', 'value' => 100]);
        PredictionIntelligence::create(['company_id' => $company->id, 'date' => '2025-06-01', 'value' => 500]);

        Sanctum::actingAs($user);

        // filtramos solo enero: deberia tener 1 registro y no poder calcular prediccion
        $res = $this->getJson("/api/dashboard?company_id={$company->id}&date_from=2025-01-01&date_to=2025-01-31");

        $res->assertStatus(200)
            ->assertJsonPath('filters_applied.date_from', '2025-01-01')
            // con 1 punto no puede predecir, debe decir "Sin datos"
            ->assertJsonPath('kpis.next_prediction.value', 'Sin datos');
    }

    /**
     * Verifica que un usuario no pueda ver el dashboard de otra empresa
     */
    public function test_usuario_no_puede_ver_dashboard_de_otra_empresa(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $companyOfUser2 = $this->createCompanyForUser($user2);

        Sanctum::actingAs($user1);

        $res = $this->getJson("/api/dashboard?company_id={$companyOfUser2->id}");

        $res->assertStatus(404);
    }
}
