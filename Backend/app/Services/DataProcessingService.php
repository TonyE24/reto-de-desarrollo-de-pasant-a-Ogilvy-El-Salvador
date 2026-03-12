<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * DataProcessingService - Issue #31 (Semana 5)
 *
 * Responsable de limpiar, normalizar y calcular métricas sobre los datos
 * antes de que lleguen a los controladores o al frontend.
 * Trabaja con arrays o colecciones de datos numericos.
 */
class DataProcessingService
{
    // ---------------------------------------------------------------
    // 1. LIMPIEZA DE DATOS
    // ---------------------------------------------------------------

    /**
     * Elimina elementos nulos o vacios de un array de datos
     * Util para limpiar series temporales con huecos
     */
    public function removeNulls(array $data): array
    {
        return array_values(
            array_filter($data, fn($value) => !is_null($value) && $value !== '')
        );
    }

    /**
     * Elimina valores duplicados de un array
     * Util para limpiar listas de keywords o competidores repetidos
     */
    public function removeDuplicates(array $data): array
    {
        return array_values(array_unique($data));
    }

    /**
     * Detecta outliers usando el metodo IQR (rango intercuartilico)
     * Un outlier es cualquier valor que este fuera de 1.5 * IQR
     * Retorna los datos SIN los outliers
     */
    public function removeOutliers(array $data): array
    {
        if (count($data) < 4) {
            return $data; // necesitamos al menos 4 puntos para calcular IQR
        }

        $sorted = $data;
        sort($sorted);

        $n  = count($sorted);
        $q1 = $sorted[(int) floor($n * 0.25)];
        $q3 = $sorted[(int) floor($n * 0.75)];
        $iqr = $q3 - $q1;

        $lowerBound = $q1 - (1.5 * $iqr);
        $upperBound = $q3 + (1.5 * $iqr);

        return array_values(
            array_filter($data, fn($v) => $v >= $lowerBound && $v <= $upperBound)
        );
    }

    // ---------------------------------------------------------------
    // 2. NORMALIZACIÓN DE DATOS
    // ---------------------------------------------------------------

    /**
     * Normaliza fechas al formato estandar Y-m-d
     * Acepta strings como "2026/03/05", "05-03-2026", o timestamps
     */
    public function normalizeDate(string $date): ?string
    {
        try {
            $parsed = date_create($date);
            return $parsed ? date_format($parsed, 'Y-m-d') : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Normaliza un array de valores numericos al rango [0, 1]
     * Usa Min-Max Scaling: valor_normalizado = (x - min) / (max - min)
     * Util para comparar metricas de diferente escala en graficos
     */
    public function minMaxScale(array $data): array
    {
        if (empty($data)) return [];

        $min = min($data);
        $max = max($data);

        // si todos los valores son iguales, evitamos division por cero
        if ($max === $min) {
            return array_fill(0, count($data), 0.0);
        }

        return array_map(fn($v) => round(($v - $min) / ($max - $min), 4), $data);
    }

    // ---------------------------------------------------------------
    // 3. CÁLCULO DE MÉTRICAS
    // ---------------------------------------------------------------

    /**
     * Calcula el promedio movil simple de una serie de datos
     * El promedio movil suaviza el ruido para ver la tendencia real
     *
     * @param array $data      Serie de valores numericos
     * @param int   $window    Tamaño de la ventana (cuantos puntos promediar)
     * @return array           Serie suavizada (misma longitud, primeros valores son null)
     */
    public function movingAverage(array $data, int $window = 3): array
    {
        $result = [];
        $n      = count($data);

        for ($i = 0; $i < $n; $i++) {
            if ($i < $window - 1) {
                // no tenemos suficientes puntos anteriores aun
                $result[] = null;
            } else {
                // promediamos los ultimos $window puntos
                $slice    = array_slice($data, $i - $window + 1, $window);
                $result[] = round(array_sum($slice) / $window, 2);
            }
        }

        return $result;
    }

    /**
     * Calcula metricas estadisticas basicas sobre un array de numeros
     * (promedio, mediana, min, max, desviacion estandar)
     * Util para los KPIs del dashboard
     */
    public function calculateStats(array $data): array
    {
        $clean = $this->removeNulls($data);

        if (empty($clean)) {
            return ['count' => 0, 'mean' => 0, 'median' => 0, 'min' => 0, 'max' => 0, 'std_dev' => 0];
        }

        $n      = count($clean);
        $mean   = array_sum($clean) / $n;

        // mediana
        $sorted = $clean;
        sort($sorted);
        $median = ($n % 2 === 0)
            ? ($sorted[$n / 2 - 1] + $sorted[$n / 2]) / 2
            : $sorted[(int) floor($n / 2)];

        // desviacion estandar poblacional
        $variance = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $clean)) / $n;
        $stdDev   = sqrt($variance);

        return [
            'count'   => $n,
            'mean'    => round($mean, 2),
            'median'  => round($median, 2),
            'min'     => min($clean),
            'max'     => max($clean),
            'std_dev' => round($stdDev, 2),
        ];
    }
}
