<?php

declare(strict_types=1);

namespace App\ComErros;

/**
 * Classe com erros PROPOSITAIS, para o PHPStan encontrar.
 */
class Pedido
{
    // Nivel 1: a variavel $totl nao existe (erro de digitacao)
    public function calcularTotal(float $preco, int $quantidade): float
    {
        $total = $preco * $quantidade;

        return $totl;
    }

    public function aplicarDesconto(float $valor, int $percentual): float
    {
        return $valor - ($valor * $percentual / 100);
    }

    // Nivel 5: passando string "10" onde o metodo espera int
    public function exemplo(): float
    {
        return $this->aplicarDesconto(100.0, '10');
    }

    // Nivel 4: $codigo ja e int, entao is_int() sempre sera verdadeiro
    public function status(int $codigo): string
    {
        if (is_int($codigo)) {
            return 'ok';
        }

        return 'erro';
    }
}
