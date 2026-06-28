<?php

namespace App\Http\Controllers;

use App\Models\Auxiliar;
use App\Models\Movimiento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MovimientoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->string('buscar')->toString();
        $tipo = $request->string('tipo')->toString();
        $auxiliarId = $request->integer('auxiliar_id');

        $movimientos = Movimiento::query()
            ->with(['auxiliar.proyecto', 'renta'])
            ->when($buscar, fn ($q) => $q->where('referencia', 'like', "%{$buscar}%")->orWhere('descripcion', 'like', "%{$buscar}%"))
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->when($auxiliarId, fn ($q) => $q->where('auxiliar_id', $auxiliarId))
            ->orderByDesc('fecha')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Banco/Movimientos/Index', [
            'movimientos' => $movimientos,
            'auxiliares' => Auxiliar::with('proyecto')->orderBy('nombre')->get(),
            'filtros' => ['buscar' => $buscar, 'tipo' => $tipo, 'auxiliar_id' => $auxiliarId ?: null],
            'resumen' => [
                'cobros' => (float) Movimiento::where('tipo', 'cobro')->sum('monto'),
                'pagos' => (float) Movimiento::where('tipo', 'pago')->sum('monto'),
                'transferencias' => (float) Movimiento::where('tipo', 'transferencia')->sum('monto'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        Movimiento::create($this->validateData($request));

        return back()->with('success', 'Movimiento registrado correctamente.');
    }

    public function update(Request $request, Movimiento $movimiento)
    {
        $movimiento->update($this->validateData($request));

        return back()->with('success', 'Movimiento actualizado correctamente.');
    }

    public function destroy(Movimiento $movimiento)
    {
        $movimiento->delete();

        return back()->with('success', 'Movimiento eliminado correctamente.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'auxiliar_id' => ['required', 'exists:auxiliares,id'],
            'tipo' => ['required', 'in:pago,transferencia,cobro'],
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha' => ['required', 'date'],
            'referencia' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
        ]);
    }
}
