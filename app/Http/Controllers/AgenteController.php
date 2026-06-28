<?php

namespace App\Http\Controllers;

use App\Models\Agente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AgenteController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->string('buscar')->toString();

        $agentes = Agente::query()
            ->when($buscar, function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('email', 'like', "%{$buscar}%");
            })
            ->withCount('seguros')
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Seguros/Agentes/Index', [
            'agentes' => $agentes,
            'filtros' => ['buscar' => $buscar],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Agente::create($data);

        return back()->with('success', 'Agente creado correctamente.');
    }

    public function update(Request $request, Agente $agente)
    {
        $data = $this->validateData($request);
        $agente->update($data);

        return back()->with('success', 'Agente actualizado correctamente.');
    }

    public function destroy(Agente $agente)
    {
        $agente->delete();

        return back()->with('success', 'Agente eliminado correctamente.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'comision' => ['required', 'numeric', 'min:0', 'max:100'],
            'activo' => ['boolean'],
        ]);
    }
}
