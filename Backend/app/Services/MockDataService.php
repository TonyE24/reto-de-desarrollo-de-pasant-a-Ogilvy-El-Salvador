<?php

namespace App\Services;

class MockDataService
{
    /**
     * Genera datos simulados para Inteligencia de Mercado
     * (Precios de competidores, cuotas de mercado, etc)
     */
    public function getMarketData(string $industry)
    {
        $products = [
            'Tecnología' => ['Suscripción Cloud', 'Laptop Pro', 'Soporte IT'],
            'Alimentos' => ['Café Especial', 'Pan Artesanal', 'Mermelada Orgánica'],
            'Comercio' => ['Pack Retail', 'Envío Express', 'Garantía Extendida']
        ];

        $currentProducts = $products[$industry] ?? ['Producto Genérico A', 'Producto Genérico B'];

        return collect($currentProducts)->map(function ($product) {
            return [
                'product' => $product,
                'my_price' => rand(50, 200),
                'competitors' => [
                    ['name' => 'Competidor Alpha', 'price' => rand(45, 210)],
                    ['name' => 'Competidor Beta', 'price' => rand(40, 190)],
                    ['name' => 'Competidor Gamma', 'price' => rand(55, 220)],
                ],
                'market_share' => rand(5, 30) . '%'
            ];
        });
    }

    /**
     * Genera datos de Tendencias y Sentimiento
     * (Keywords que estan sonando y que tan feliz esta la gente)
     */
    public function getTrendData()
    {
        $keywords = ['Sustentabilidad', 'IA Generativa', 'Ecommerce Local', 'Delivery Gratis', 'Calidad Premium'];

        return collect($keywords)->map(function ($word) {
            return [
                'keyword' => $word,
                'volume' => rand(1000, 50000),
                'sentiment' => [
                    'positive' => $p = rand(40, 80),
                    'neutral' => $n = rand(10, 20),
                    'negative' => 100 - ($p + $n)
                ],
                'trend' => rand(0, 1) ? 'up' : 'down'
            ];
        });
    }

    /**
     * Genera datos de Prediccion (Series hitoricas)
     * (Ventas proyectadas para los proximos meses)
     * Son simples proyecciones lineales con algo de ruido para dar realismo
     */
    public function getPredictionData()
    {
        $months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'];
        $baseVentas = 1000;

        return collect($months)->map(function ($month) use (&$baseVentas) {
            $baseVentas += rand(-100, 300); // va subiendo un poco cada mes
            return [
                'month' => $month,
                'actual' => $baseVentas,
                'predicted' => $baseVentas + rand(50, 150)
            ];
        });
    }

    /**
     * Genera Oportunidades de Innovacion
     */
    public function getInnovationData()
    {
        return [
            [
                'title' => 'Nicho Desatendido',
                'description' => 'Hay un 20% de aumento en busquedas de empaques biodegradables en tu zona.',
                'impact' => 'Alto'
            ],
            [
                'title' => 'Optimización de Precios',
                'description' => 'Tus competidores subieron precios un 5%, podrías ajustar tu margen sin perder clientes.',
                'impact' => 'Medio'
            ]
        ];
    }
}
