<?php

declare(strict_types=1);

namespace App\Corrigido;

/**
 * Mesma classe, mas com os erros corrigidos.
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

    public function saudacao(): string
    {
        return 'Ola, ' . $this->getNome();
    }

    public function emailEmMaiusculo(): string
    {
        if ($this->email === null) {
            return '';
        }

        return strtoupper($this->email);
    }
}