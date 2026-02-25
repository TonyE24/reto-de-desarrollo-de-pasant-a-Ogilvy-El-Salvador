<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MockDataService;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class IntelligenceController extends Controller
{
    protected $mockData;

    // inyectamos el servicio de mock data para tenerlo disponible
    public function __construct(MockDataService $mockData)
    {
        $this->mockData = $mockData;
    }

    /**
     * Endpoint para Inteligencia de Mercado
     * Retorna comparativa de precios y competidores
     */
    public function getMarketData(Request $request)
    {
        $companyId = $request->query('company_id');

        // verificamos que la empresa exista y sea del usuario que pregunta
        $company = Auth::user()->companies()->find($companyId);

        if (!$company) {
            return response()->json(['message' => 'Empresa no encontrada o no tienes acceso'], 404);
        }

        // le pedimos los datos al servicio de mock data segun la industria de la empresa
        $data = $this->mockData->getMarketData($company->industry);

        return response()->json([
            'company_name' => $company->name,
            'industry' => $company->industry,
            'market_analysis' => $data
        ]);
    }
}
