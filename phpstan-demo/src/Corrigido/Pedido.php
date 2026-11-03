<?php

declare(strict_types=1);

namespace App\Corrigido;

/**
 * Mesma classe, mas com os erros corrigidos.
 */
class Pedido
{
    public function calcularTotal(float $preco, int $quantidade): float
    {
        $total = $preco * $quantidade;

        return $total;
    }

    public function aplicarDesconto(float $valor, int $percentual): float
    {
        return $valor - ($valor * $percentual / 100);
    }

    public function exemplo(): float
    {
        return $this->aplicarDesconto(100.0, 10);
    }

    public function status(int $codigo): string
    {
        if ($codigo > 0) {
            return 'ok';
        }

        return 'erro';
    }
}
