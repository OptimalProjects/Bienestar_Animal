<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SciIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected string $validToken = 'test-sci-token-secret';

    protected function setUp(): void
    {
        parent::setUp();

        config(['sci.api_token' => $this->validToken]);
        config(['sci.app_id' => 'bienestar-animal']);
    }

    // ==========================================
    // Autenticacion
    // ==========================================

    public function test_kpis_sin_token_retorna_401(): void
    {
        $response = $this->getJson('/api/sci/kpis');

        $response->assertStatus(401);
        $response->assertJsonStructure(['app_id', 'error', 'timestamp']);
    }

    public function test_kpis_con_token_invalido_retorna_401(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer token-invalido',
        ])->getJson('/api/sci/kpis');

        $response->assertStatus(401);
    }

    public function test_data_sin_token_retorna_401(): void
    {
        $response = $this->getJson('/api/sci/data');

        $response->assertStatus(401);
    }

    // ==========================================
    // Endpoint KPIs
    // ==========================================

    public function test_kpis_retorna_estructura_correcta(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->validToken}",
        ])->getJson('/api/sci/kpis');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'app_id',
            'timestamp',
            'kpis' => [
                '*' => ['label', 'value'],
            ],
        ]);
        $response->assertJsonPath('app_id', 'bienestar-animal');
    }

    public function test_kpis_contiene_los_4_indicadores_obligatorios(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->validToken}",
        ])->getJson('/api/sci/kpis');

        $response->assertStatus(200);

        $kpis = $response->json('kpis');
        $labels = array_column($kpis, 'label');

        $this->assertContains('total_animales', $labels);
        $this->assertContains('adopciones_mes', $labels);
        $this->assertContains('denuncias_activas', $labels);
        $this->assertContains('consultas_hoy', $labels);
    }

    // ==========================================
    // Endpoint Data
    // ==========================================

    public function test_data_retorna_estructura_correcta(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->validToken}",
        ])->getJson('/api/sci/data');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'app_id',
            'timestamp',
            'data' => [
                'resumen' => [
                    'total_animales',
                    'animales_en_refugio',
                    'adopciones_completadas',
                    'denuncias_resueltas',
                ],
                'tendencias' => [
                    'adopciones_vs_mes_anterior',
                    'denuncias_nuevas_mes',
                    'animales_nuevos_mes',
                ],
            ],
        ]);
        $response->assertJsonPath('app_id', 'bienestar-animal');
    }

    public function test_data_tipo_detalle_incluye_detalle(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->validToken}",
        ])->getJson('/api/sci/data?tipo=detalle');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'resumen',
                'tendencias',
                'detalle' => [
                    'animales',
                    'adopciones',
                    'denuncias',
                    'veterinaria',
                ],
            ],
        ]);
    }

    public function test_data_con_filtro_de_fechas(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->validToken}",
        ])->getJson('/api/sci/data?fecha_inicio=2026-01-01&fecha_fin=2026-01-31');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'app_id',
            'timestamp',
            'data' => [
                'resumen',
                'tendencias',
            ],
        ]);
    }

    public function test_data_tipo_resumen_no_incluye_detalle(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->validToken}",
        ])->getJson('/api/sci/data?tipo=resumen');

        $response->assertStatus(200);
        $response->assertJsonMissing(['detalle']);
    }
}
