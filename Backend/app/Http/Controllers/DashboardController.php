<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\LinearRegressionHelper;
use App\Services\MockDataService;

class DashboardController extends Controller
{
    protected $mockData;

    public function __construct(MockDataService $mockData)
    {
        $this->mockData = $mockData;
    }

    /**
     * Endpoint principal del dashboard: consolida los KPIs de todos los modulos
     * GET /api/dashboard?company_id={id}&date_from={date}&date_to={date}
     */
    public function index(Request $request)
    {
        $companyId = $request->query('company_id');
        $dateFrom  = $request->query('date_from');
        $dateTo    = $request->query('date_to');

        // validamos que la empresa le pertenezca al usuario autenticado
        $company = Auth::user()->companies()->find($companyId);

        if (!$company) {
            return response()->json(['message' => 'Empresa no encontrada'], 404);
        }

        // --- KPI 1: Cuota de mercado (sacamos el promedio de los datos de mercado) ---
        $marketData = $this->mockData->getMarketData($company->industry);
        $marketShareValues = $marketData->pluck('market_share')->map(function ($val) {
            // los valores vienen como "17%" entonces le quitamos el simbolo
            return (int) str_replace('%', '', $val);
        });
        $avgMarketShare = $marketShareValues->avg() ?? 0;

        // --- KPI 2: Tendencia de sentimiento (promedio del sentimiento positivo) ---
        $trendData = $this->mockData->getTrendData();
        $avgPositiveSentiment = $trendData->pluck('sentiment.positive')->avg() ?? 0;
        $sentimentLabel = $avgPositiveSentiment >= 60 ? 'Positivo'
            : ($avgPositiveSentiment >= 40 ? 'Neutral' : 'Negativo');

        // --- KPI 3: Prediccion del proximo mes (ultimo valor del algoritmo) ---
        $historicalRecords = $company->predictionData()
            ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->where('date', '<=', $dateTo))
            ->orderBy('date', 'asc')
            ->get();

        $nextMonthPrediction = null;
        if ($historicalRecords->count() >= 2) {
            $values = $historicalRecords->pluck('value')->toArray();
            $projected = LinearRegressionHelper::predict($values, 1);
            $nextMonthPrediction = round($projected[0] ?? 0, 2);
        }

        // --- KPI 4: Oportunidades de innovacion activas ---
        $innovationCount = $company->innovationData()->count();
        // si no hay datos reales usamos el mock para que no aparezca 0
        if ($innovationCount === 0) {
            $innovationCount = count($this->mockData->getInnovationData());
        }

        return response()->json([
            'company_name' => $company->name,
            'filters_applied' => [
                'date_from' => $dateFrom,
                'date_to'   => $dateTo,
            ],
            'kpis' => [
                'market_share' => [
                    'value' => round($avgMarketShare, 1) . '%',
                    'label' => 'Cuota de Mercado Promedio',
                    'trend' => $avgMarketShare >= 15 ? 'up' : 'down',
                ],
                'sentiment' => [
                    'value' => round($avgPositiveSentiment, 1) . '%',
                    'label' => 'Sentimiento Positivo',
                    'status' => $sentimentLabel,
                ],
                'next_prediction' => [
                    'value' => $nextMonthPrediction !== null ? '$' . $nextMonthPrediction : 'Sin datos',
                    'label' => 'Prediccion Proximo Mes',
                ],
                'active_opportunities' => [
                    'value' => $innovationCount,
                    'label' => 'Oportunidades Activas',
                ],
            ],
        ]);
    }
}
