<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProyectoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->string('buscar')->toString();

        $proyectos = Proyecto::query()
            ->when($buscar, fn ($q) => $q->where('nombre', 'like', "%{$buscar}%"))
            ->withCount('auxiliares')
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Banco/Proyectos/Index', [
            'proyectos' => $proyectos,
            'filtros' => ['buscar' => $buscar],
        ]);
    }

    public function store(Request $request)
    {
        Proyecto::create($this->validateData($request));

        return back()->with('success', 'Proyecto creado correctamente.');
    }

    public function update(Request $request, Proyecto $proyecto)
    {
        $proyecto->update($this->validateData($request));

        return back()->with('success', 'Proyecto actualizado correctamente.');
    }

    public function destroy(Proyecto $proyecto)
    {
        $proyecto->delete();

        return back()->with('success', 'Proyecto eliminado correctamente.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
        ]);
    }
}
