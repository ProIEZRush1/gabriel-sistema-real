<?php

namespace App\Http\Controllers;

use App\Models\Auxiliar;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuxiliarController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->string('buscar')->toString();
        $proyectoId = $request->integer('proyecto_id');

        $auxiliares = Auxiliar::query()
            ->with('proyecto')
            ->withCount('movimientos')
            ->when($buscar, fn ($q) => $q->where('nombre', 'like', "%{$buscar}%")->orWhere('banco', 'like', "%{$buscar}%"))
            ->when($proyectoId, fn ($q) => $q->where('proyecto_id', $proyectoId))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Banco/Auxiliares/Index', [
            'auxiliares' => $auxiliares,
            'proyectos' => Proyecto::orderBy('nombre')->get(['id', 'nombre']),
            'filtros' => ['buscar' => $buscar, 'proyecto_id' => $proyectoId ?: null],
        ]);
    }

    public function store(Request $request)
    {
        Auxiliar::create($this->validateData($request));

        return back()->with('success', 'Auxiliar creado correctamente.');
    }

    public function update(Request $request, Auxiliar $auxiliar)
    {
        $auxiliar->update($this->validateData($request));

        return back()->with('success', 'Auxiliar actualizado correctamente.');
    }

    public function destroy(Auxiliar $auxiliar)
    {
        $auxiliar->delete();

        return back()->with('success', 'Auxiliar eliminado correctamente.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'proyecto_id' => ['required', 'exists:proyectos,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'banco' => ['nullable', 'string', 'max:255'],
            'numero_cuenta' => ['nullable', 'string', 'max:100'],
            'saldo_inicial' => ['required', 'numeric'],
        ]);
    }
}
