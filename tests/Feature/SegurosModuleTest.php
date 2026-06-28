<?php

namespace Tests\Feature;

use App\Models\Agente;
use App\Models\Seguro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SegurosModuleTest extends TestCase
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

    public function test_crea_y_persiste_un_agente(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('agentes.store'), [
            'nombre' => 'Laura Méndez',
            'email' => 'laura@overcloud.us',
            'telefono' => '55-0000-1111',
            'comision' => 10,
            'activo' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('agentes', ['nombre' => 'Laura Méndez', 'comision' => 10]);
        $this->assertSame('laura@overcloud.us', Agente::first()->email);
    }

    public function test_crea_lee_actualiza_y_borra_un_seguro(): void
    {
        $this->actingAs($this->admin());
        $agente = Agente::create(['nombre' => 'Agente A', 'comision' => 5]);

        // Crear
        $this->post(route('seguros.store'), [
            'poliza' => 'POL-TEST-1',
            'tipo' => 'auto',
            'aseguradora' => 'Aseguradora X',
            'asegurado' => 'Cliente Uno',
            'beneficiario' => 'Beneficiario Uno',
            'condiciones' => 'Cobertura total',
            'suma_asegurada' => 500000,
            'prima' => 12000,
            'vigencia_inicio' => '2026-01-01',
            'vigencia_fin' => '2026-12-31',
            'estado' => 'activo',
            'agente_id' => $agente->id,
        ])->assertRedirect();

        // Leer de vuelta desde la BD
        $seguro = Seguro::where('poliza', 'POL-TEST-1')->first();
        $this->assertNotNull($seguro);
        $this->assertSame('auto', $seguro->tipo);
        $this->assertSame($agente->id, $seguro->agente_id);

        // Actualizar
        $this->put(route('seguros.update', $seguro), array_merge($seguro->toArray(), [
            'estado' => 'cancelado',
            'prima' => 9999,
        ]))->assertRedirect();
        $this->assertSame('cancelado', $seguro->fresh()->estado);

        // Borrar
        $this->delete(route('seguros.destroy', $seguro))->assertRedirect();
        $this->assertDatabaseMissing('seguros', ['poliza' => 'POL-TEST-1']);
    }

    public function test_compara_cotizaciones_y_selecciona_la_mejor(): void
    {
        $this->actingAs($this->admin());
        $seguro = Seguro::create([
            'poliza' => 'POL-COT', 'tipo' => 'inmueble', 'asegurado' => 'Cliente',
            'suma_asegurada' => 1000000, 'prima' => 20000, 'estado' => 'activo',
        ]);

        $this->post(route('cotizaciones.store'), [
            'seguro_id' => $seguro->id, 'aseguradora' => 'A', 'prima' => 20000, 'cobertura' => 1000000,
        ])->assertRedirect();
        $this->post(route('cotizaciones.store'), [
            'seguro_id' => $seguro->id, 'aseguradora' => 'B', 'prima' => 18000, 'cobertura' => 1000000,
        ])->assertRedirect();

        $this->assertDatabaseCount('cotizaciones', 2);

        $mejor = $seguro->cotizaciones()->where('aseguradora', 'B')->first();
        $this->put(route('cotizaciones.seleccionar', $mejor))->assertRedirect();

        $this->assertTrue($mejor->fresh()->seleccionada);
        $this->assertFalse($seguro->cotizaciones()->where('aseguradora', 'A')->first()->seleccionada);
    }

    public function test_los_modulos_requieren_autenticacion(): void
    {
        $this->get(route('seguros.index'))->assertRedirect(route('login'));
    }
}
