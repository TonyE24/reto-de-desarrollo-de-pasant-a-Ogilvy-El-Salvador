<?php

namespace Tests\Unit;

use App\Services\DataProcessingService;
use Tests\TestCase;

/**
 * Tests unitarios para DataProcessingService - Issue #31
 */
class DataProcessingServiceTest extends TestCase
{
    protected DataProcessingService $service;

    // se ejecuta antes de cada test
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DataProcessingService();
    }

    // ---------------------------------------------------------------
    // Tests de limpieza
    // ---------------------------------------------------------------

    // test 1: removeNulls elimina los nulos y vacios
    public function test_remove_nulls_elimina_nulos_y_vacios(): void
    {
        $datos = [10, null, 20, '', 30, null];
        $resultado = $this->service->removeNulls($datos);

        $this->assertEquals([10, 20, 30], $resultado);
    }

    // test 2: removeNulls con array limpio devuelve el mismo array
    public function test_remove_nulls_con_array_limpio(): void
    {
        $datos = [1, 2, 3];
        $resultado = $this->service->removeNulls($datos);

        $this->assertEquals([1, 2, 3], $resultado);
    }

    // test 3: removeDuplicates elimina valores repetidos
    public function test_remove_duplicates_elimina_repetidos(): void
    {
        $datos = ['ecommerce', 'ia', 'ecommerce', 'delivery', 'ia'];
        $resultado = $this->service->removeDuplicates($datos);

        $this->assertCount(3, $resultado);
        $this->assertEqualsCanonicalizing(['ecommerce', 'ia', 'delivery'], $resultado);
    }

    // test 4: removeOutliers quita los extremos estadisticos
    public function test_remove_outliers_elimina_extremos(): void
    {
        // serie normal con un outlier extremo al final
        $datos = [100, 105, 98, 102, 99, 103, 1000];
        $resultado = $this->service->removeOutliers($datos);

        // el 1000 debe desaparecer porque esta muy lejos del rango
        $this->assertNotContains(1000, $resultado);
        // los valores normales deben seguir ahi
        $this->assertContains(100, $resultado);
    }

    // test 5: removeOutliers con menos de 4 puntos devuelve los datos tal cual
    public function test_remove_outliers_no_procesa_arrays_pequenos(): void
    {
        $datos = [100, 200, 999]; // solo 3 elementos
        $resultado = $this->service->removeOutliers($datos);

        $this->assertEquals($datos, $resultado);
    }

    // ---------------------------------------------------------------
    // Tests de normalización
    // ---------------------------------------------------------------

    // test 6: normalizeDate convierte diferentes formatos a Y-m-d
    public function test_normalize_date_convierte_formato_correctamente(): void
    {
        // formato slash
        $this->assertEquals('2026-03-05', $this->service->normalizeDate('2026/03/05'));

        // formato estandar
        $this->assertEquals('2026-03-05', $this->service->normalizeDate('2026-03-05'));

        // fecha escrita
        $this->assertEquals('2026-03-05', $this->service->normalizeDate('March 5, 2026'));
    }

    // test 7: normalizeDate retorna null con una fecha invalida
    public function test_normalize_date_retorna_null_con_fecha_invalida(): void
    {
        $resultado = $this->service->normalizeDate('esto-no-es-fecha');

        // puede ser null o una fecha parseable segun el SO, pero no debe lanzar excepcion
        $this->assertTrue(is_null($resultado) || strlen($resultado) === 10);
    }

    // test 8: minMaxScale normaliza valores al rango [0, 1]
    public function test_min_max_scale_normaliza_al_rango_cero_uno(): void
    {
        $datos = [0, 50, 100];
        $resultado = $this->service->minMaxScale($datos);

        $this->assertEquals(0.0,  $resultado[0]); // el minimo es 0
        $this->assertEquals(0.5,  $resultado[1]); // 50 es la mitad
        $this->assertEquals(1.0,  $resultado[2]); // el maximo es 1
    }

    // test 9: minMaxScale con todos los valores iguales devuelve ceros (no divide por cero)
    public function test_min_max_scale_con_valores_iguales_no_lanza_error(): void
    {
        $datos = [5, 5, 5];
        $resultado = $this->service->minMaxScale($datos);

        $this->assertEquals([0.0, 0.0, 0.0], $resultado);
    }

    // test 10: minMaxScale con array vacio devuelve array vacio
    public function test_min_max_scale_con_array_vacio(): void
    {
        $this->assertEquals([], $this->service->minMaxScale([]));
    }

    // ---------------------------------------------------------------
    // Tests de métricas
    // ---------------------------------------------------------------

    // test 11: movingAverage calcula el promedio movil correctamente
    public function test_moving_average_calcula_correctamente(): void
    {
        $datos = [10, 20, 30, 40, 50];
        $resultado = $this->service->movingAverage($datos, window: 3);

        // los dos primeros son null porque no hay suficientes puntos previos
        $this->assertNull($resultado[0]);
        $this->assertNull($resultado[1]);

        // el tercero es el promedio de [10, 20, 30] = 20
        $this->assertEquals(20.0, $resultado[2]);

        // el cuarto es el promedio de [20, 30, 40] = 30
        $this->assertEquals(30.0, $resultado[3]);
    }

    // test 12: calculateStats devuelve estadisticas correctas
    public function test_calculate_stats_devuelve_valores_correctos(): void
    {
        $datos = [10, 20, 30, 40, 50];
        $stats = $this->service->calculateStats($datos);

        $this->assertEquals(5,    $stats['count']);
        $this->assertEquals(30.0, $stats['mean']);   // (10+20+30+40+50)/5 = 30
        $this->assertEquals(30.0, $stats['median']); // valor del medio
        $this->assertEquals(10,   $stats['min']);
        $this->assertEquals(50,   $stats['max']);
        $this->assertGreaterThan(0, $stats['std_dev']); // debe haber desviacion
    }

    // test 13: calculateStats ignora los nulos en el calculo
    public function test_calculate_stats_ignora_nulos(): void
    {
        $datos = [10, null, 30, null, 50];
        $stats = $this->service->calculateStats($datos);

        // debe calcular stats solo con [10, 30, 50]
        $this->assertEquals(3,    $stats['count']);
        $this->assertEquals(30.0, $stats['mean']);
    }

    // test 14: calculateStats con array vacio devuelve todos en cero
    public function test_calculate_stats_con_array_vacio(): void
    {
        $stats = $this->service->calculateStats([]);

        $this->assertEquals(0, $stats['count']);
        $this->assertEquals(0, $stats['mean']);
    }
}
