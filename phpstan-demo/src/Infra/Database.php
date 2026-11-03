<?php

declare(strict_types=1);

namespace App\Infra;

use PDO;

final class Database
{
    public static function conectar(): PDO
    {
        $host = getenv('DB_HOST') ?: 'db';
        $nome = getenv('DB_NAME') ?: 'phpstan_demo';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: 'root';

        return new PDO(
            "mysql:host={$host};dbname={$nome};charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
}
