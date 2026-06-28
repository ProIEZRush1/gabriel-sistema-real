<?php

namespace App\Http\Controllers;

use App\Models\Inquilino;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InquilinoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->string('buscar')->toString();

        $inquilinos = Inquilino::query()
            ->when($buscar, function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('email', 'like', "%{$buscar}%")
                    ->orWhere('telefono', 'like', "%{$buscar}%");
            })
            ->withCount('rentas')
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Rentas/Inquilinos/Index', [
            'inquilinos' => $inquilinos,
            'filtros' => ['buscar' => $buscar],
        ]);
    }

    public function store(Request $request)
    {
        Inquilino::create($this->validateData($request));

        return back()->with('success', 'Inquilino creado correctamente.');
    }

    public function update(Request $request, Inquilino $inquilino)
    {
        $inquilino->update($this->validateData($request));

        return back()->with('success', 'Inquilino actualizado correctamente.');
    }

    public function destroy(Inquilino $inquilino)
    {
        $inquilino->delete();

        return back()->with('success', 'Inquilino eliminado correctamente.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'identificacion' => ['nullable', 'string', 'max:100'],
        ]);
    }
}
