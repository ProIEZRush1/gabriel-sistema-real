<?php

namespace Tests\Feature;

use App\Models\Auxiliar;
use App\Models\Inquilino;
use App\Models\Propiedad;
use App\Models\Proyecto;
use App\Models\Renta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RentasModuleTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Gabriel Chernitsky',
            'email' => 'gabriel-chernitsky@overcloud.us',
            'password' => Hash::make('2OFqZ17eNcNP'),
        ]);
    }

    public function test_crea_propiedad_e_inquilino_y_persisten(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('propiedades.store'), [
            'nombre' => 'Casa Roma', 'tipo' => 'casa', 'direccion' => 'Calle 1',
            'propietario' => 'Gabriel', 'renta_mensual' => 18000, 'estado' => 'disponible',
        ])->assertRedirect();
        $this->assertDatabaseHas('propiedades', ['nombre' => 'Casa Roma', 'renta_mensual' => 18000]);

        $this->post(route('inquilinos.store'), [
            'nombre' => 'Pedro Ruiz', 'email' => 'pedro@example.com', 'telefono' => '555', 'identificacion' => 'RUIP01',
        ])->assertRedirect();
        $this->assertDatabaseHas('inquilinos', ['nombre' => 'Pedro Ruiz']);
    }

    public function test_genera_renta_y_la_lee_desde_la_bd(): void
    {
        $this->actingAs($this->admin());
        $propiedad = Propiedad::create(['nombre' => 'Depto', 'tipo' => 'departamento', 'renta_mensual' => 25000, 'estado' => 'rentada']);
        $inquilino = Inquilino::create(['nombre' => 'Inquilino']);

        $this->post(route('rentas.store'), [
            'propiedad_id' => $propiedad->id,
            'inquilino_id' => $inquilino->id,
            'periodo' => '2026-06',
            'monto' => 25000,
            'fecha_emision' => '2026-06-01',
            'fecha_vencimiento' => '2026-06-05',
            'tasa_moratoria' => 3,
        ])->assertRedirect();

        $renta = Renta::first();
        $this->assertNotNull($renta);
        $this->assertSame($propiedad->id, $renta->propiedad_id);
        // Vencimiento 2026-06-05 ya pasó (hoy 2026-06-28) => con adeudo automático.
        $this->assertSame('con_adeudo', $renta->estado);
        $this->assertGreaterThan(0, $renta->dias_atraso);
        $this->assertGreaterThan(0, $renta->interes_calculado);
    }

    public function test_cobrar_renta_calcula_interes_y_crea_movimiento_bancario(): void
    {
        $this->actingAs($this->admin());
        $propiedad = Propiedad::create(['nombre' => 'Depto', 'tipo' => 'departamento', 'renta_mensual' => 25000, 'estado' => 'rentada']);
        $inquilino = Inquilino::create(['nombre' => 'Inquilino']);
        $proyecto = Proyecto::create(['nombre' => 'Proyecto']);
        $auxiliar = Auxiliar::create(['proyecto_id' => $proyecto->id, 'nombre' => 'Cuenta', 'saldo_inicial' => 0]);

        $renta = Renta::create([
            'propiedad_id' => $propiedad->id, 'inquilino_id' => $inquilino->id, 'periodo' => '2026-05',
            'monto' => 25000, 'fecha_emision' => '2026-05-01', 'fecha_vencimiento' => '2026-05-05',
            'tasa_moratoria' => 3, 'estado' => 'con_adeudo',
        ]);
        $interesEsperado = $renta->interes_calculado;
        $this->assertGreaterThan(0, $interesEsperado);

        $this->put(route('rentas.cobrar', $renta), [
            'fecha_pago' => '2026-06-28',
            'auxiliar_id' => $auxiliar->id,
        ])->assertRedirect();

        $renta->refresh();
        $this->assertSame('cobrada', $renta->estado);
        $this->assertEquals($interesEsperado, (float) $renta->interes_moratorio);
        $this->assertEquals(25000, (float) $renta->monto_pagado);

        // Movimiento interconectado en el auxiliar bancario.
        $this->assertDatabaseHas('movimientos', [
            'renta_id' => $renta->id,
            'auxiliar_id' => $auxiliar->id,
            'tipo' => 'cobro',
        ]);
        $mov = $auxiliar->movimientos()->first();
        $this->assertEquals(25000 + $interesEsperado, (float) $mov->monto);
    }

    public function test_filtra_rentas_por_estado(): void
    {
        $this->actingAs($this->admin());
        $propiedad = Propiedad::create(['nombre' => 'Depto', 'tipo' => 'departamento', 'renta_mensual' => 1000, 'estado' => 'rentada']);
        $inquilino = Inquilino::create(['nombre' => 'Inquilino']);
        Renta::create(['propiedad_id' => $propiedad->id, 'inquilino_id' => $inquilino->id, 'periodo' => '2026-06', 'monto' => 1000, 'fecha_emision' => '2026-06-01', 'fecha_vencimiento' => '2026-06-05', 'tasa_moratoria' => 0, 'estado' => 'cobrada', 'monto_pagado' => 1000, 'fecha_pago' => '2026-06-04']);

        $this->get(route('rentas.index', ['estado' => 'cobrada']))->assertOk();
    }
}
