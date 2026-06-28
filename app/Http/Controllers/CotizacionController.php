<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Seguro;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CotizacionController extends Controller
{
    public function index(Request $request)
    {
        $seguroId = $request->integer('seguro_id');

        $cotizacions = Cotizacion::query()
            ->with('seguro')
            ->when($seguroId, fn ($q) => $q->where('seguro_id', $seguroId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Seguros/Cotizaciones/Index', [
            'cotizaciones' => $cotizacions,
            'seguros' => Seguro::orderBy('poliza')->get(['id', 'poliza', 'asegurado', 'tipo']),
            'filtros' => ['seguro_id' => $seguroId ?: null],
        ]);
    }

    public function store(Request $request)
    {
        Cotizacion::create($this->validateData($request));

        return back()->with('success', 'Cotización registrada correctamente.');
    }

    public function update(Request $request, Cotizacion $cotizacion)
    {
        $cotizacion->update($this->validateData($request));

        return back()->with('success', 'Cotización actualizada correctamente.');
    }

    public function destroy(Cotizacion $cotizacion)
    {
        $cotizacion->delete();

        return back()->with('success', 'Cotización eliminada correctamente.');
    }

    /**
     * Marca una cotización como seleccionada (la mejor) y deselecciona las demás del mismo seguro.
     */
    public function seleccionar(Cotizacion $cotizacion)
    {
        Cotizacion::where('seguro_id', $cotizacion->seguro_id)->update(['seleccionada' => false]);
        $cotizacion->update(['seleccionada' => true]);

        return back()->with('success', 'Cotización seleccionada.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'seguro_id' => ['required', 'exists:seguros,id'],
            'aseguradora' => ['required', 'string', 'max:255'],
            'prima' => ['required', 'numeric', 'min:0'],
            'cobertura' => ['required', 'numeric', 'min:0'],
            'condiciones' => ['nullable', 'string'],
        ]);
    }
}
