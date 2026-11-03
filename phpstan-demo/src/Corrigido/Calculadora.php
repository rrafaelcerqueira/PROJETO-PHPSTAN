<?php

declare(strict_types=1);

namespace App\Corrigido;

/**
 * Mesma classe, mas com os erros corrigidos.
 */
class Calculadora
{
    public function somar(int $a, int $b): int
    {
        return $a + $b;
    }

    /**
     * @param array<int, float|int> $numeros
     */
    public function media(array $numeros): float
    {
        if ($numeros === []) {
            return 0.0;
        }

        return array_sum($numeros) / count($numeros);
    }

    public function descricao(): string
    {
        return (string) $this->somar(1, 2);
    }
}
