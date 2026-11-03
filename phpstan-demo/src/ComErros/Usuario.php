<?php

declare(strict_types=1);

namespace App\ComErros;

/**
 * Classe com erros PROPOSITAIS, para o PHPStan encontrar.
 */
class Usuario
{
    private string $nome;

    private ?string $email;

    public function __construct(string $nome, ?string $email = null)
    {
        $this->nome = $nome;
        $this->email = $email;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    // Nivel 0: o metodo getNomeCompleto() nao existe
    public function saudacao(): string
    {
        return 'Ola, ' . $this->getNomeCompleto();
    }

    // Nivel 8: $this->email pode ser null e strtoupper() nao aceita null
    public function emailEmMaiusculo(): string
    {
        return strtoupper($this->email);
    }
}