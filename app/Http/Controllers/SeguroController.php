<?php

namespace App\Http\Controllers;

use App\Models\Agente;
use App\Models\Seguro;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SeguroController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->string('buscar')->toString();
        $tipo = $request->string('tipo')->toString();

        $seguros = Seguro::query()
            ->with('agente')
            ->withCount('cotizaciones')
            ->when($buscar, function ($q) use ($buscar) {
                $q->where(function ($sub) use ($buscar) {
                    $sub->where('poliza', 'like', "%{$buscar}%")
                        ->orWhere('asegurado', 'like', "%{$buscar}%")
                        ->orWhere('aseguradora', 'like', "%{$buscar}%");
                });
            })
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Seguros/Index', [
            'seguros' => $seguros,
            'agentes' => Agente::orderBy('nombre')->get(['id', 'nombre']),
            'filtros' => ['buscar' => $buscar, 'tipo' => $tipo],
            'resumen' => [
                'total' => Seguro::count(),
                'inmueble' => Seguro::where('tipo', 'inmueble')->count(),
                'auto' => Seguro::where('tipo', 'auto')->count(),
                'medico' => Seguro::where('tipo', 'medico')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        Seguro::create($this->validateData($request));

        return back()->with('success', 'Seguro registrado correctamente.');
    }

    public function update(Request $request, Seguro $seguro)
    {
        $seguro->update($this->validateData($request));

        return back()->with('success', 'Seguro actualizado correctamente.');
    }

    public function destroy(Seguro $seguro)
    {
        $seguro->delete();

        return back()->with('success', 'Seguro eliminado correctamente.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'poliza' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'in:inmueble,auto,medico'],
            'aseguradora' => ['nullable', 'string', 'max:255'],
            'asegurado' => ['required', 'string', 'max:255'],
            'beneficiario' => ['nullable', 'string', 'max:255'],
            'condiciones' => ['nullable', 'string'],
            'suma_asegurada' => ['required', 'numeric', 'min:0'],
            'prima' => ['required', 'numeric', 'min:0'],
            'vigencia_inicio' => ['nullable', 'date'],
            'vigencia_fin' => ['nullable', 'date'],
            'estado' => ['required', 'in:activo,vencido,cancelado'],
            'agente_id' => ['nullable', 'exists:agentes,id'],
        ]);
    }
}
