<?php

namespace Database\Seeders;

use App\Models\Agente;
use App\Models\Auxiliar;
use App\Models\Cotizacion;
use App\Models\Inquilino;
use App\Models\Movimiento;
use App\Models\Propiedad;
use App\Models\Proyecto;
use App\Models\Renta;
use App\Models\Seguro;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database. Idempotente.
     */
    public function run(): void
    {
        // ----- Usuario administrador -----
        User::updateOrCreate(
            ['email' => 'gabriel-chernitsky@overcloud.us'],
            [
                'name' => 'Gabriel Chernitsky',
                'password' => Hash::make('2OFqZ17eNcNP'),
                'email_verified_at' => now(),
            ]
        );

        // ----- Datos de ejemplo (sólo si no existen) -----
        if (Agente::count() === 0) {
            $agente = Agente::create([
                'nombre' => 'María López',
                'email' => 'maria.lopez@overcloud.us',
                'telefono' => '55-1234-5678',
                'comision' => 8.5,
                'activo' => true,
            ]);

            $seguro = Seguro::create([
                'poliza' => 'POL-0001',
                'tipo' => 'inmueble',
                'aseguradora' => 'Seguros del Valle',
                'asegurado' => 'Gabriel Chernitsky',
                'beneficiario' => 'Familia Chernitsky',
                'condiciones' => 'Cobertura amplia contra incendio, robo y daños por agua.',
                'suma_asegurada' => 2500000,
                'prima' => 18500,
                'vigencia_inicio' => Carbon::parse('2026-01-01'),
                'vigencia_fin' => Carbon::parse('2026-12-31'),
                'estado' => 'activo',
                'agente_id' => $agente->id,
            ]);

            Cotizacion::create([
                'seguro_id' => $seguro->id,
                'aseguradora' => 'Seguros del Valle',
                'prima' => 18500,
                'cobertura' => 2500000,
                'condiciones' => 'Deducible 5%.',
                'seleccionada' => true,
            ]);
            Cotizacion::create([
                'seguro_id' => $seguro->id,
                'aseguradora' => 'Aseguradora Nacional',
                'prima' => 19900,
                'cobertura' => 2500000,
                'condiciones' => 'Deducible 3%.',
                'seleccionada' => false,
            ]);
        }

        if (Propiedad::count() === 0) {
            $propiedad = Propiedad::create([
                'nombre' => 'Departamento Polanco 4B',
                'tipo' => 'departamento',
                'direccion' => 'Av. Presidente Masaryk 123, CDMX',
                'propietario' => 'Gabriel Chernitsky',
                'renta_mensual' => 25000,
                'estado' => 'rentada',
            ]);

            $inquilino = Inquilino::create([
                'nombre' => 'Juan Pérez',
                'email' => 'juan.perez@example.com',
                'telefono' => '55-9876-5432',
                'identificacion' => 'PEPJ800101',
            ]);

            Renta::create([
                'propiedad_id' => $propiedad->id,
                'inquilino_id' => $inquilino->id,
                'periodo' => '2026-06',
                'monto' => 25000,
                'fecha_emision' => Carbon::parse('2026-06-01'),
                'fecha_vencimiento' => Carbon::parse('2026-06-05'),
                'fecha_pago' => null,
                'monto_pagado' => 0,
                'tasa_moratoria' => 3,
                'interes_moratorio' => 0,
                'estado' => 'con_adeudo',
            ]);
        }

        if (Proyecto::count() === 0) {
            $proyecto = Proyecto::create([
                'nombre' => 'Edificio Polanco',
                'descripcion' => 'Control de movimientos del edificio en Polanco.',
            ]);

            $auxiliar = Auxiliar::create([
                'proyecto_id' => $proyecto->id,
                'nombre' => 'Cuenta operativa BBVA',
                'banco' => 'BBVA',
                'numero_cuenta' => '0123456789',
                'saldo_inicial' => 100000,
            ]);

            Movimiento::create([
                'auxiliar_id' => $auxiliar->id,
                'tipo' => 'cobro',
                'monto' => 25000,
                'fecha' => Carbon::parse('2026-06-05'),
                'referencia' => 'Renta 2026-05',
                'descripcion' => 'Cobro de renta mayo.',
            ]);
        }
    }
}
