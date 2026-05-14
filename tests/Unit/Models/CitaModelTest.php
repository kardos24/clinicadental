<?php

namespace Tests\Unit\Models;

use App\Models\Cita;
use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitaModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_estado_label_para_todos_los_estados(): void
    {
        $estados = [
            'pendiente'     => 'Pendiente',
            'confirmada'    => 'Confirmada',
            'cancelada'     => 'Cancelada',
            'realizada'     => 'Realizada',
            'no_presentado' => 'No se presentó',
        ];

        foreach ($estados as $estado => $esperado) {
            $cita = new Cita(['estado' => $estado]);
            $this->assertSame($esperado, $cita->estado_label, "Fallo en estado: {$estado}");
        }
    }

    public function test_estado_color_para_todos_los_estados(): void
    {
        $colores = [
            'pendiente'     => '#f97316',
            'confirmada'    => '#3b82f6',
            'cancelada'     => '#ef4444',
            'realizada'     => '#4ade80',
            'no_presentado' => '#6b7280',
        ];

        foreach ($colores as $estado => $esperado) {
            $cita = new Cita(['estado' => $estado]);
            $this->assertSame($esperado, $cita->estado_color, "Fallo en estado: {$estado}");
        }
    }

    public function test_scope_proximas_excluye_pasadas_y_finalizadas(): void
    {
        $cliente = Cliente::factory()->create();

        // Solo esta debe aparecer
        Cita::factory()->create(['cliente_id' => $cliente->id, 'fecha_hora' => now()->addDays(2)->format('Y-m-d H:i:s'), 'estado' => 'pendiente']);
        // Pasada → excluida
        Cita::factory()->create(['cliente_id' => $cliente->id, 'fecha_hora' => now()->subDays(1)->format('Y-m-d H:i:s'), 'estado' => 'pendiente']);
        // Futura pero cancelada → excluida
        Cita::factory()->create(['cliente_id' => $cliente->id, 'fecha_hora' => now()->addDays(3)->format('Y-m-d H:i:s'), 'estado' => 'cancelada']);
        // Futura pero realizada → excluida
        Cita::factory()->create(['cliente_id' => $cliente->id, 'fecha_hora' => now()->addDays(3)->format('Y-m-d H:i:s'), 'estado' => 'realizada']);

        $resultado = Cita::proximas()->get();

        $this->assertCount(1, $resultado);
    }
}
