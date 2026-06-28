<?php

namespace App\Http\Controllers;

use App\Models\Propiedad;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PropiedadController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->string('buscar')->toString();
        $estado = $request->string('estado')->toString();

        $propiedades = Propiedad::query()
            ->when($buscar, function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('direccion', 'like', "%{$buscar}%")
                    ->orWhere('propietario', 'like', "%{$buscar}%");
            })
            ->when($estado, fn ($q) => $q->where('estado', $estado))
            ->withCount('rentas')
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Rentas/Propiedades/Index', [
            'propiedades' => $propiedades,
            'filtros' => ['buscar' => $buscar, 'estado' => $estado],
        ]);
    }

    public function store(Request $request)
    {
        Propiedad::create($this->validateData($request));

        return back()->with('success', 'Propiedad creada correctamente.');
    }

    public function update(Request $request, Propiedad $propiedad)
    {
        $propiedad->update($this->validateData($request));

        return back()->with('success', 'Propiedad actualizada correctamente.');
    }

    public function destroy(Propiedad $propiedad)
    {
        $propiedad->delete();

        return back()->with('success', 'Propiedad eliminada correctamente.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'propietario' => ['nullable', 'string', 'max:255'],
            'renta_mensual' => ['required', 'numeric', 'min:0'],
            'estado' => ['required', 'in:disponible,rentada,mantenimiento'],
        ]);
    }
}
