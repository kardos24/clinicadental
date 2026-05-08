<?php

namespace Tests\Unit\Models;

use App\Models\Dentadura;
use Tests\TestCase;

class DentaduraModelTest extends TestCase
{
    private function diente(array $attrs = []): Dentadura
    {
        $d = new Dentadura();
        foreach ($attrs as $k => $v) {
            $d->$k = $v;
        }
        return $d;
    }

    // ─── estaPresente ────────────────────────────────────────────────────────

    public function test_esta_presente_cuando_estado_es_null(): void
    {
        $this->assertTrue($this->diente(['num_diente' => '16'])->estaPresente());
    }

    public function test_esta_presente_cuando_estado_es_presente(): void
    {
        $this->assertTrue($this->diente(['num_diente' => '16', 'estado_pieza' => 'presente'])->estaPresente());
    }

    public function test_no_presente_cuando_ausente(): void
    {
        $this->assertFalse($this->diente(['num_diente' => '16', 'estado_pieza' => 'ausente'])->estaPresente());
    }

    public function test_no_presente_cuando_corona(): void
    {
        $this->assertFalse($this->diente(['num_diente' => '16', 'estado_pieza' => 'corona'])->estaPresente());
    }

    // ─── estadoCara ──────────────────────────────────────────────────────────

    public function test_estado_cara_devuelve_sano_cuando_null(): void
    {
        $d = $this->diente(['num_diente' => '16', 'cara_vestibular' => null]);
        $this->assertSame('sano', $d->estadoCara('vestibular'));
    }

    public function test_estado_cara_devuelve_valor_real(): void
    {
        $d = $this->diente(['num_diente' => '16', 'cara_vestibular' => 'caries']);
        $this->assertSame('caries', $d->estadoCara('vestibular'));
    }

    // ─── colorCara ───────────────────────────────────────────────────────────

    public function test_color_cara_caries(): void
    {
        $d = $this->diente(['num_diente' => '16', 'cara_vestibular' => 'caries']);
        $this->assertSame('#ef4444', $d->colorCara('vestibular'));
    }

    public function test_color_cara_sano(): void
    {
        $d = $this->diente(['num_diente' => '16', 'cara_vestibular' => null]);
        $this->assertSame('#4ade80', $d->colorCara('vestibular'));
    }

    // ─── tieneOclusal ────────────────────────────────────────────────────────

    public function test_tiene_oclusal_molar(): void
    {
        $this->assertTrue($this->diente(['num_diente' => '16'])->tieneOclusal());
    }

    public function test_no_tiene_oclusal_incisivo(): void
    {
        $this->assertFalse($this->diente(['num_diente' => '11'])->tieneOclusal());
    }

    public function test_no_tiene_oclusal_canino(): void
    {
        $this->assertFalse($this->diente(['num_diente' => '13'])->tieneOclusal());
    }

    // ─── carasAplicables ─────────────────────────────────────────────────────

    public function test_molar_tiene_5_caras(): void
    {
        $caras = $this->diente(['num_diente' => '16'])->carasAplicables();
        $this->assertCount(5, $caras);
        $this->assertContains('oclusal', $caras);
    }

    public function test_incisivo_tiene_4_caras(): void
    {
        $caras = $this->diente(['num_diente' => '11'])->carasAplicables();
        $this->assertCount(4, $caras);
        $this->assertNotContains('oclusal', $caras);
    }

    // ─── tienePatologia ──────────────────────────────────────────────────────

    public function test_no_tiene_patologia_cuando_todo_sano(): void
    {
        $this->assertFalse($this->diente(['num_diente' => '16'])->tienePatologia());
    }

    public function test_tiene_patologia_cuando_una_cara_tiene_caries(): void
    {
        $d = $this->diente(['num_diente' => '16', 'cara_vestibular' => 'caries']);
        $this->assertTrue($d->tienePatologia());
    }

    // ─── resumen ─────────────────────────────────────────────────────────────

    public function test_resumen_diente_ausente(): void
    {
        $d = $this->diente(['num_diente' => '16', 'estado_pieza' => 'ausente']);
        $this->assertSame('Ausente', $d->resumen());
    }

    public function test_resumen_diente_sano(): void
    {
        $d = $this->diente(['num_diente' => '16']);
        $this->assertSame('Sano', $d->resumen());
    }

    public function test_resumen_lista_patologias(): void
    {
        $d = $this->diente(['num_diente' => '16', 'cara_vestibular' => 'caries']);
        $this->assertStringContainsString('Vestibular: Caries', $d->resumen());
    }
}
