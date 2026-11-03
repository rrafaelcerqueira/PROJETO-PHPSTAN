<?php

declare(strict_types=1);

namespace App\ComErros;

/**
 * Classe com erros PROPOSITAIS, para o PHPStan encontrar.
 */
class Calculadora
{
    public function somar(int $a, int $b): int
    {
        return $a + $b;
    }

    // Nivel 6: falta dizer o tipo dos itens do array (array<int, float>)
    public function media(array $numeros): float
    {
        return array_sum($numeros) / count($numeros);
    }

    // Nivel 3: o metodo promete devolver string, mas devolve int
    public function descricao(): string
    {
        return $this->somar(1, 2);
    }
}
