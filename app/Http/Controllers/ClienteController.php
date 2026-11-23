<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->query('buscar', ''));

        $consulta = User::where('rol', 'cliente');

        if ($buscar !== '') {
            $consulta->where(function ($query) use ($buscar): void {
                $query->where('name', 'like', '%'.$buscar.'%')
                    ->orWhere('email', 'like', '%'.$buscar.'%');
            });
        }

        $clientes = $consulta->latest()->paginate(15);

        return view('admin.clientes.index', compact('clientes', 'buscar'));
    }
}
