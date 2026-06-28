<?php

namespace App\Http\Controllers;

use App\Models\Agente;
use App\Models\Auxiliar;
use App\Models\Movimiento;
use App\Models\Propiedad;
use App\Models\Proyecto;
use App\Models\Renta;
use App\Models\Seguro;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Estado de pago automático: marca rentas vencidas como con adeudo.
        Renta::where('estado', '!=', 'cobrada')
            ->whereDate('fecha_vencimiento', '<', now())
            ->update(['estado' => 'con_adeudo']);

        return Inertia::render('Dashboard', [
            'metricas' => [
                'seguros' => Seguro::count(),
                'agentes' => Agente::count(),
                'propiedades' => Propiedad::count(),
                'rentas' => Renta::count(),
                'rentas_con_adeudo' => Renta::where('estado', 'con_adeudo')->count(),
                'rentas_cobradas' => Renta::where('estado', 'cobrada')->count(),
                'proyectos' => Proyecto::count(),
                'auxiliares' => Auxiliar::count(),
                'movimientos' => Movimiento::count(),
                'total_cobrado' => (float) Renta::where('estado', 'cobrada')->sum('monto_pagado'),
                'interes_acumulado' => (float) Renta::sum('interes_moratorio'),
                'suma_asegurada' => (float) Seguro::sum('suma_asegurada'),
            ],
            'ultimas_rentas' => Renta::with(['propiedad', 'inquilino'])
                ->latest()->take(5)->get(),
            'ultimos_movimientos' => Movimiento::with('auxiliar')
                ->latest()->take(5)->get(),
        ]);
    }
}
