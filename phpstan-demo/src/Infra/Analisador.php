<?php

declare(strict_types=1);

namespace App\Infra;

use PDO;
use RuntimeException;

/**
 * Roda o PHPStan pela linha de comando e le o resultado em JSON.
 */
final class Analisador
{
    public const PASTAS = [
        'ComErros' => 'src/ComErros',
        'Corrigido' => 'src/Corrigido',
    ];

    public function __construct(private readonly string $raiz)
    {
    }

    /**
     * @return array{total: int, erros: list<array{arquivo: string, linha: int, mensagem: string, identificador: string}>, comando: string}
     */
    public function rodar(string $pasta, int $nivel): array
    {
        if (!isset(self::PASTAS[$pasta])) {
            throw new RuntimeException('Pasta invalida.');
        }

        $nivel = max(0, min(9, $nivel));
        $alvo = self::PASTAS[$pasta];

        $comando = sprintf(
            '%s analyse %s --level=%d --memory-limit=512M --no-progress --error-format=json -c %s',
            escapeshellarg($this->raiz . '/vendor/bin/phpstan'),
            escapeshellarg($alvo),
            $nivel,
            escapeshellarg($this->raiz . '/phpstan.neon')
        );

        $processo = proc_open(
            $comando,
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $this->raiz
        );

        if (!is_resource($processo)) {
            throw new RuntimeException('Nao foi possivel iniciar o PHPStan.');
        }

        $saida = (string) stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($processo);

        /** @var mixed $json */
        $json = json_decode($saida, true);
        if (!is_array($json)) {
            throw new RuntimeException('Resposta inesperada do PHPStan: ' . substr($saida, 0, 300));
        }

        $erros = [];
        $arquivos = is_array($json['files'] ?? null) ? $json['files'] : [];
        foreach ($arquivos as $caminho => $dados) {
            foreach ($dados['messages'] ?? [] as $msg) {
                $erros[] = [
                    'arquivo' => ltrim(str_replace($this->raiz, '', (string) $caminho), '/'),
                    'linha' => (int) ($msg['line'] ?? 0),
                    'mensagem' => (string) ($msg['message'] ?? ''),
                    'identificador' => (string) ($msg['identifier'] ?? ''),
                ];
            }
        }

        return [
            'total' => count($erros),
            'erros' => $erros,
            'comando' => 'vendor/bin/phpstan analyse ' . $alvo . ' --level=' . $nivel,
        ];
    }

    /**
     * Guarda o resultado no MySQL (aparece no HeidiSQL).
     *
     * @param list<array{arquivo: string, linha: int, mensagem: string, identificador: string}> $erros
     */
    public function salvar(PDO $pdo, string $pasta, int $nivel, array $erros): void
    {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO analises (nivel, pasta, total_erros) VALUES (?, ?, ?)');
        $stmt->execute([$nivel, $pasta, count($erros)]);
        $analiseId = (int) $pdo->lastInsertId();

        $stmtErro = $pdo->prepare('INSERT INTO erros (analise_id, arquivo, linha, mensagem) VALUES (?, ?, ?, ?)');
        foreach ($erros as $erro) {
            $stmtErro->execute([$analiseId, $erro['arquivo'], $erro['linha'], $erro['mensagem']]);
        }

        $pdo->commit();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function historico(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT id, pasta, nivel, total_erros, criada_em FROM analises ORDER BY id DESC LIMIT 10');
        if ($stmt === false) {
            return [];
        }

        return array_values($stmt->fetchAll());
    }
}
