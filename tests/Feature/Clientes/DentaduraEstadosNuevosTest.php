<?php

namespace Tests\Feature\Clientes;

use App\Models\Dentadura;
use Tests\TestCase;

class DentaduraEstadosNuevosTest extends TestCase
{
    /** @test */
    public function estados_pieza_contiene_los_27_estados_requeridos(): void
    {
        $requeridos = [
            'presente', 'ausente', 'corona', 'puente', 'implante', 'endodoncia',
            'no_erupcionado', 'extrac_indicada', 'extraido', 'temporal',
            'pulpitis', 'necrosis', 'apicectomia', 'incluido', 'supernumerario',
            'movilidad_1', 'movilidad_2', 'movilidad_3',
            'carilla', 'pilar_puente', 'pontico', 'prot_removible',
            'giroversion', 'migracion', 'diastema', 'fluorosis', 'agenesia',
        ];

        foreach ($requeridos as $estado) {
            $this->assertArrayHasKey(
                $estado,
                Dentadura::ESTADOS_PIEZA,
                "Falta el estado de pieza: {$estado}"
            );
        }
    }

    /** @test */
    public function estados_cara_contiene_los_13_estados_requeridos(): void
    {
        $requeridos = [
            'sano', 'caries', 'obturacion', 'fractura', 'sellante', 'desgaste',
            'caries_det', 'composite', 'amalgama', 'erosion',
            'tincion', 'fisura', 'reconstruccion',
        ];

        foreach ($requeridos as $estado) {
            $this->assertArrayHasKey(
                $estado,
                Dentadura::ESTADOS_CARA,
                "Falta el estado de cara: {$estado}"
            );
        }
    }

    /** @test */
    public function cada_estado_pieza_tiene_label_color_e_icono(): void
    {
        foreach (Dentadura::ESTADOS_PIEZA as $key => $data) {
            $this->assertArrayHasKey('label', $data, "Estado pieza '{$key}' sin label");
            $this->assertArrayHasKey('color', $data, "Estado pieza '{$key}' sin color");
            $this->assertArrayHasKey('icono', $data, "Estado pieza '{$key}' sin icono");
        }
    }

    /** @test */
    public function cada_estado_cara_tiene_label_y_color(): void
    {
        foreach (Dentadura::ESTADOS_CARA as $key => $data) {
            $this->assertArrayHasKey('label', $data, "Estado cara '{$key}' sin label");
            $this->assertArrayHasKey('color', $data, "Estado cara '{$key}' sin color");
        }
    }
}
