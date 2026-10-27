<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (! $user) {
            $adminEmails = array_map('trim', explode(',', env('ADMIN_EMAILS', '')));
            $empleadoEmails = array_map('trim', explode(',', env('EMPLEADO_EMAILS', '')));

            $rolAsignado = match (true) {
               in_array($googleUser->getEmail(), $adminEmails) => 'administrador',
               in_array($googleUser->getEmail(), $empleadoEmails) => 'empleado',
               default => 'cliente',
            };
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => null,
                'rol' => $rolAsignado,
            ]);
        } elseif (! $user->google_id) {
            $user->update(['google_id' => $googleUser->getId()]);
        }

        Auth::login($user);

        return match ($user->rol) {
            'administrador' => redirect('/admin/dashboard'),
            'empleado' => redirect('/admin/pedidos'),
            default => redirect('/menu'),
        };
    }
}
