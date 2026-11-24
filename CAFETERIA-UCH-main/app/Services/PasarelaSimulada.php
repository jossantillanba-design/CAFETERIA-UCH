<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;

class PasarelaSimulada
{
    private const RECHAZOS_TARJETA = [
        '4000040000000008' => 'Fondos insuficientes.',
        '4000020000000000' => 'Tarjeta reportada como robada.',
        '5400020000000003' => 'El CVV es incorrecto.',
    ];

    public function cobrarTarjeta(string $numero, string $vencimiento): array
    {
        $numero = preg_replace('/\D/', '', $numero);

        if (! $this->luhnValido($numero)) {
            return $this->rechazo('El número de tarjeta no es válido.');
        }

        if ($this->vencida($vencimiento)) {
            return $this->rechazo('La tarjeta está vencida.');
        }

        if (isset(self::RECHAZOS_TARJETA[$numero])) {
            return $this->rechazo(self::RECHAZOS_TARJETA[$numero]);
        }

        return $this->aprobado($this->marca($numero) . ' •••• ' . substr($numero, -4));
    }

    public function cobrarBilletera(string $billetera, string $celular, string $codigo): array
    {
        $celular = preg_replace('/\D/', '', $celular);

        if (! preg_match('/^9\d{8}$/', $celular)) {
            return $this->rechazo('El número de celular no es válido.');
        }

        if ($codigo === '000000') {
            return $this->rechazo('El código de aprobación es incorrecto.');
        }

        if ($celular === '900000002') {
            return $this->rechazo('Saldo insuficiente en la billetera.');
        }

        return $this->aprobado(ucfirst($billetera) . ' ••• ' . substr($celular, -3));
    }

    private function aprobado(string $referencia): array
    {
        return [
            'aprobado' => true,
            'codigo' => 'SIM-' . strtoupper(Str::random(10)),
            'mensaje' => 'Pago aprobado.',
            'referencia' => $referencia,
        ];
    }

    private function rechazo(string $mensaje): array
    {
        return [
            'aprobado' => false,
            'codigo' => null,
            'mensaje' => $mensaje,
            'referencia' => null,
        ];
    }

    private function luhnValido(string $numero): bool
    {
        $largo = strlen($numero);
        if ($largo < 13 || $largo > 19) {
            return false;
        }

        $suma = 0;
        $duplicar = false;
        for ($i = $largo - 1; $i >= 0; $i--) {
            $digito = (int) $numero[$i];
            if ($duplicar) {
                $digito *= 2;
                if ($digito > 9) {
                    $digito -= 9;
                }
            }
            $suma += $digito;
            $duplicar = ! $duplicar;
        }

        return $suma % 10 === 0;
    }

    private function vencida(string $vencimiento): bool
    {
        [$mes, $anio] = array_map('intval', explode('/', $vencimiento));

        return Carbon::create(2000 + $anio, $mes, 1)->endOfMonth()->isPast();
    }

    private function marca(string $numero): string
    {
        return match (true) {
            str_starts_with($numero, '4') => 'Visa',
            preg_match('/^(5[1-5]|2[2-7])/', $numero) === 1 => 'Mastercard',
            preg_match('/^3[47]/', $numero) === 1 => 'Amex',
            default => 'Tarjeta',
        };
    }
}