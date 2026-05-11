<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Dentadura;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DentaduraEndpointEstadosTest extends TestCase
{
    use RefreshDatabase;

    private User $gestor;
    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gestor  = User::factory()->create(['role' => 'gestor']);
        $this->cliente = Cliente::create([
            'nombre'    => 'Test',
            'apellidos' => 'Paciente',
            'user_id'   => null,
        ]);
        Dentadura::create([
            'cliente_id'   => $this->cliente->id,
            'num_diente'   => '16',
            'estado_pieza' => 'presente',
        ]);
    }

    /** @test */
    public function gestor_puede_guardar_estados_de_cara_nuevos(): void
    {
        $response = $this->actingAs($this->gestor)
            ->postJson(route('clientes.dentadura', $this->cliente), [
                'dientes' => [[
                    'num_diente'      => '16',
                    'estado_pieza'    => 'endodoncia',
                    'cara_vestibular' => 'caries_det',
                    'cara_lingual'    => 'composite',
                    'cara_mesial'     => 'sano',
                    'cara_distal'     => 'sano',
                    'cara_oclusal'    => 'amalgama',
                ]],
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('dentadura', [
            'cliente_id'      => $this->cliente->id,
            'num_diente'      => '16',
            'estado_pieza'    => 'endodoncia',
            'cara_vestibular' => 'caries_det',
            'cara_lingual'    => 'composite',
            'cara_oclusal'    => 'amalgama',
        ]);
    }

    /** @test */
    public function gestor_puede_guardar_todos_los_estados_pieza_nuevos(): void
    {
        $nuevos = [
            'no_erupcionado','extrac_indicada','extraido','temporal',
            'pulpitis','necrosis','apicectomia','incluido','supernumerario',
            'movilidad_1','movilidad_2','movilidad_3','carilla','pilar_puente',
            'pontico','prot_removible','giroversion','migracion','diastema',
            'fluorosis','agenesia',
        ];
        foreach ($nuevos as $estado) {
            $r = $this->actingAs($this->gestor)
                ->postJson(route('clientes.dentadura', $this->cliente), [
                    'dientes' => [[
                        'num_diente'   => '16',
                        'estado_pieza' => $estado,
                    ]],
                ]);
            $r->assertOk("Falló para estado_pieza: {$estado}");
        }
    }

    /** @test */
    public function cliente_no_puede_guardar_dentadura(): void
    {
        $userCliente = User::factory()->create(['role' => 'cliente']);
        $r = $this->actingAs($userCliente)
            ->postJson(route('clientes.dentadura', $this->cliente), [
                'dientes' => [[
                    'num_diente'   => '16',
                    'estado_pieza' => 'caries_det',
                ]],
            ]);
        $r->assertForbidden();
    }
}
