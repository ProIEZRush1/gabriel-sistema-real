<?php

namespace Tests\Feature;

use App\Models\Auxiliar;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BancoModuleTest extends TestCase
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

    public function test_crea_proyecto_con_varios_auxiliares(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('proyectos.store'), ['nombre' => 'Edificio Centro', 'descripcion' => 'Desc'])->assertRedirect();
        $proyecto = Proyecto::first();
        $this->assertNotNull($proyecto);

        // Varios auxiliares por proyecto.
        $this->post(route('auxiliares.store'), ['proyecto_id' => $proyecto->id, 'nombre' => 'Cuenta BBVA', 'banco' => 'BBVA', 'numero_cuenta' => '111', 'saldo_inicial' => 50000])->assertRedirect();
        $this->post(route('auxiliares.store'), ['proyecto_id' => $proyecto->id, 'nombre' => 'Cuenta Santander', 'banco' => 'Santander', 'numero_cuenta' => '222', 'saldo_inicial' => 30000])->assertRedirect();

        $this->assertSame(2, $proyecto->auxiliares()->count());
    }

    public function test_registra_movimientos_y_calcula_saldo(): void
    {
        $this->actingAs($this->admin());
        $proyecto = Proyecto::create(['nombre' => 'P']);
        $auxiliar = Auxiliar::create(['proyecto_id' => $proyecto->id, 'nombre' => 'Cuenta', 'saldo_inicial' => 100000]);

        $this->post(route('movimientos.store'), ['auxiliar_id' => $auxiliar->id, 'tipo' => 'cobro', 'monto' => 25000, 'fecha' => '2026-06-10', 'referencia' => 'R1'])->assertRedirect();
        $this->post(route('movimientos.store'), ['auxiliar_id' => $auxiliar->id, 'tipo' => 'pago', 'monto' => 5000, 'fecha' => '2026-06-11', 'referencia' => 'P1'])->assertRedirect();
        $this->post(route('movimientos.store'), ['auxiliar_id' => $auxiliar->id, 'tipo' => 'transferencia', 'monto' => 10000, 'fecha' => '2026-06-12', 'referencia' => 'T1'])->assertRedirect();

        $this->assertDatabaseCount('movimientos', 3);
        // 100000 + 25000 - 5000 - 10000 = 110000
        $this->assertEquals(110000, $auxiliar->fresh()->saldo_actual);
    }

    public function test_actualiza_y_borra_movimiento(): void
    {
        $this->actingAs($this->admin());
        $proyecto = Proyecto::create(['nombre' => 'P']);
        $auxiliar = Auxiliar::create(['proyecto_id' => $proyecto->id, 'nombre' => 'Cuenta', 'saldo_inicial' => 0]);

        $this->post(route('movimientos.store'), ['auxiliar_id' => $auxiliar->id, 'tipo' => 'cobro', 'monto' => 1000, 'fecha' => '2026-06-10'])->assertRedirect();
        $mov = $auxiliar->movimientos()->first();

        $this->put(route('movimientos.update', $mov), ['auxiliar_id' => $auxiliar->id, 'tipo' => 'cobro', 'monto' => 2000, 'fecha' => '2026-06-10'])->assertRedirect();
        $this->assertEquals(2000, (float) $mov->fresh()->monto);

        $this->delete(route('movimientos.destroy', $mov))->assertRedirect();
        $this->assertDatabaseMissing('movimientos', ['id' => $mov->id]);
    }
}
