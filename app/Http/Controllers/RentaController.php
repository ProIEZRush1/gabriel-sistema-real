<?php

namespace App\Http\Controllers;

use App\Models\Auxiliar;
use App\Models\Inquilino;
use App\Models\Movimiento;
use App\Models\Propiedad;
use App\Models\Renta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RentaController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->string('buscar')->toString();
        $estado = $request->string('estado')->toString();

        // Recalcula estados (estado de pago automático) antes de listar.
        $this->actualizarEstados();

        $rentas = Renta::query()
            ->with(['propiedad', 'inquilino'])
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->when($buscar, function ($q) use ($buscar) {
                $q->where('periodo', 'like', "%{$buscar}%")
                    ->orWhereHas('inquilino', fn ($s) => $s->where('nombre', 'like', "%{$buscar}%"))
                    ->orWhereHas('propiedad', fn ($s) => $s->where('nombre', 'like', "%{$buscar}%"));
            })
            ->orderByDesc('fecha_vencimiento')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Rentas/Index', [
            'rentas' => $rentas,
            'propiedades' => Propiedad::orderBy('nombre')->get(['id', 'nombre', 'renta_mensual']),
            'inquilinos' => Inquilino::orderBy('nombre')->get(['id', 'nombre']),
            'auxiliares' => Auxiliar::with('proyecto')->orderBy('nombre')->get(),
            'filtros' => ['buscar' => $buscar, 'estado' => $estado],
            'reporte' => $this->reporte(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $renta = new Renta($data);
        $renta->recalcularEstado();
        $renta->save();

        return back()->with('success', 'Renta generada correctamente.');
    }

    public function update(Request $request, Renta $renta)
    {
        $data = $this->validateData($request);
        $renta->fill($data);
        $renta->recalcularEstado();
        $renta->save();

        return back()->with('success', 'Renta actualizada correctamente.');
    }

    public function destroy(Renta $renta)
    {
        $renta->delete();

        return back()->with('success', 'Renta eliminada correctamente.');
    }

    /**
     * Registra el cobro de una renta: calcula el interés moratorio, marca como cobrada
     * y (opcionalmente) genera el movimiento bancario interconectado.
     */
    public function cobrar(Request $request, Renta $renta)
    {
        $data = $request->validate([
            'fecha_pago' => ['required', 'date'],
            'auxiliar_id' => ['nullable', 'exists:auxiliares,id'],
        ]);

        DB::transaction(function () use ($renta, $data) {
            $interes = $renta->interes_calculado;
            $total = (float) $renta->monto + $interes;

            $renta->fecha_pago = $data['fecha_pago'];
            $renta->interes_moratorio = $interes;
            $renta->monto_pagado = $renta->monto;
            $renta->estado = 'cobrada';
            $renta->save();

            if (! empty($data['auxiliar_id'])) {
                Movimiento::create([
                    'auxiliar_id' => $data['auxiliar_id'],
                    'renta_id' => $renta->id,
                    'tipo' => 'cobro',
                    'monto' => $total,
                    'fecha' => $data['fecha_pago'],
                    'referencia' => 'Renta '.$renta->periodo,
                    'descripcion' => 'Cobro de renta (incluye interés moratorio de $'.number_format($interes, 2).')',
                ]);
            }
        });

        return back()->with('success', 'Renta cobrada correctamente.');
    }

    private function actualizarEstados(): void
    {
        Renta::where('estado', '!=', 'cobrada')
            ->whereDate('fecha_vencimiento', '<', now())
            ->update(['estado' => 'con_adeudo']);
    }

    private function reporte(): array
    {
        return [
            'generadas' => Renta::where('estado', 'generada')->count(),
            'cobradas' => Renta::where('estado', 'cobrada')->count(),
            'con_adeudo' => Renta::where('estado', 'con_adeudo')->count(),
            'total_cobrado' => (float) Renta::where('estado', 'cobrada')->sum('monto_pagado'),
            'interes_acumulado' => (float) Renta::sum('interes_moratorio'),
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'propiedad_id' => ['required', 'exists:propiedades,id'],
            'inquilino_id' => ['required', 'exists:inquilinos,id'],
            'periodo' => ['required', 'string', 'max:20'],
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha_emision' => ['required', 'date'],
            'fecha_vencimiento' => ['required', 'date'],
            'fecha_pago' => ['nullable', 'date'],
            'monto_pagado' => ['nullable', 'numeric', 'min:0'],
            'tasa_moratoria' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
    }
}
