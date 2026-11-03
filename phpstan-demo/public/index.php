<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Infra\Analisador;
use App\Infra\Database;

$raiz = dirname(__DIR__);
$analisador = new Analisador($raiz);

$pasta = (string) ($_POST['pasta'] ?? 'ComErros');
$nivel = (int) ($_POST['nivel'] ?? 5);
$resultado = null;
$erroExecucao = null;
$avisoBanco = null;
$historico = [];

$pdo = null;
try {
    $pdo = Database::conectar();
} catch (PDOException $e) {
    $avisoBanco = 'Banco de dados indisponivel: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $resultado = $analisador->rodar($pasta, $nivel);
        if ($pdo !== null) {
            $analisador->salvar($pdo, $pasta, $nivel, $resultado['erros']);
        }
    } catch (Throwable $e) {
        $erroExecucao = $e->getMessage();
    }
}

if ($pdo !== null) {
    try {
        $historico = $analisador->historico($pdo);
    } catch (PDOException $e) {
        $avisoBanco = 'Erro ao ler o historico: ' . $e->getMessage();
    }
}

function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/**
 * @param list<array{arquivo: string, linha: int, mensagem: string, identificador: string}> $erros
 * @return array<string, array<int, string>> arquivo => [linha => mensagem]
 */
function agruparPorArquivo(array $erros): array
{
    $grupos = [];
    foreach ($erros as $erro) {
        $grupos[$erro['arquivo']][$erro['linha']] = $erro['mensagem'];
    }

    return $grupos;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PHPStan na pratica</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; margin: 0; background: #f4f6f9; color: #1f2937; line-height: 1.5; }
        header { background: #4f5b93; color: #fff; padding: 28px 20px; }
        header h1 { margin: 0 0 6px; }
        main { max-width: 980px; margin: 0 auto; padding: 20px; }
        section { background: #fff; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        h2 { margin-top: 0; }
        .fluxo { display: flex; gap: 10px; flex-wrap: wrap; }
        .passo { flex: 1 1 180px; background: #eef1fb; border-radius: 8px; padding: 12px; }
        .passo b { display: block; color: #4f5b93; }
        form { display: flex; gap: 12px; flex-wrap: wrap; align-items: end; }
        label { display: flex; flex-direction: column; font-size: .9rem; gap: 4px; }
        select, button { padding: 8px 12px; font-size: 1rem; border-radius: 6px; border: 1px solid #c5cad6; }
        button { background: #4f5b93; color: #fff; border: 0; cursor: pointer; }
        button:hover { background: #3f4a7a; }
        table { width: 100%; border-collapse: collapse; font-size: .92rem; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        th { background: #f9fafb; }
        .ok { background: #dcfce7; color: #166534; padding: 10px 14px; border-radius: 8px; }
        .erro { background: #fee2e2; color: #991b1b; padding: 10px 14px; border-radius: 8px; }
        .aviso { background: #fef9c3; color: #854d0e; padding: 10px 14px; border-radius: 8px; margin-bottom: 12px; }
        code, pre { font-family: ui-monospace, Consolas, monospace; font-size: .85rem; }
        pre.cmd { background: #1f2937; color: #e5e7eb; padding: 10px 14px; border-radius: 8px; overflow-x: auto; }
        .codigo { background: #1f2937; color: #e5e7eb; border-radius: 8px; overflow-x: auto; margin: 8px 0 16px; }
        .linha { display: block; padding: 0 12px; white-space: pre; }
        .linha.bad { background: #7f1d1d; }
        .linha .n { display: inline-block; width: 2.5em; color: #9ca3af; }
        .linha .msg { color: #fca5a5; }
    </style>
</head>
<body>
<header>
    <h1>PHPStan na pratica</h1>
    <div>Analise estatica de codigo PHP: encontra erros sem executar o programa.</div>
</header>
<main>
    <section>
        <h2>Como funciona o fluxo</h2>
        <div class="fluxo">
            <div class="passo"><b>1. Codigo</b>Voce escreve classes PHP normalmente.</div>
            <div class="passo"><b>2. Analise</b>O PHPStan le o codigo e confere tipos, metodos e variaveis, sem rodar nada.</div>
            <div class="passo"><b>3. Relatorio</b>Ele lista cada erro com arquivo, linha e mensagem.</div>
            <div class="passo"><b>4. Correcao</b>Voce corrige e roda de novo ate zerar os erros.</div>
        </div>
        <p>O <b>nivel</b> vai de 0 (so o basico) ate 9 (muito rigoroso). Quanto maior o nivel, mais erros ele encontra.</p>
    </section>

    <section>
        <h2>Rodar o PHPStan</h2>
        <form method="post">
            <label>Codigo a analisar
                <select name="pasta">
                    <?php foreach (array_keys(Analisador::PASTAS) as $p): ?>
                        <option value="<?= e($p) ?>" <?= $p === $pasta ? 'selected' : '' ?>>
                            <?= e($p === 'ComErros' ? 'src/ComErros (com erros)' : 'src/Corrigido (corrigido)') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Nivel
                <select name="nivel">
                    <?php for ($i = 0; $i <= 9; $i++): ?>
                        <option value="<?= $i ?>" <?= $i === $nivel ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </label>
            <button type="submit">Analisar</button>
        </form>
    </section>

    <?php if ($erroExecucao !== null): ?>
        <section><div class="erro"><?= e($erroExecucao) ?></div></section>
    <?php endif; ?>

    <?php if ($resultado !== null): ?>
        <section>
            <h2>Resultado</h2>
            <pre class="cmd">$ <?= e($resultado['comando']) ?></pre>

            <?php if ($resultado['total'] === 0): ?>
                <div class="ok">[OK] Nenhum erro encontrado neste nivel.</div>
            <?php else: ?>
                <div class="erro"><?= $resultado['total'] ?> erro(s) encontrado(s).</div>

                <table>
                    <thead><tr><th>Arquivo</th><th>Linha</th><th>Mensagem</th></tr></thead>
                    <tbody>
                    <?php foreach ($resultado['erros'] as $erro): ?>
                        <tr>
                            <td><code><?= e($erro['arquivo']) ?></code></td>
                            <td><?= $erro['linha'] ?></td>
                            <td><?= e($erro['mensagem']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <h3>Onde esta cada erro</h3>
                <?php foreach (agruparPorArquivo($resultado['erros']) as $arquivo => $linhasComErro): ?>
                    <strong><?= e($arquivo) ?></strong>
                    <div class="codigo">
                        <?php
                        $fonte = @file($raiz . '/' . $arquivo, FILE_IGNORE_NEW_LINES);
                        foreach (($fonte !== false ? $fonte : []) as $idx => $texto):
                            $num = $idx + 1;
                            $ruim = isset($linhasComErro[$num]);
                            ?>
                            <span class="linha<?= $ruim ? ' bad' : '' ?>"><span class="n"><?= $num ?></span><?= e($texto) ?><?php if ($ruim): ?>   <span class="msg">&larr; <?= e($linhasComErro[$num]) ?></span><?php endif; ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <section>
        <h2>Historico (salvo no MySQL)</h2>
        <?php if ($avisoBanco !== null): ?>
            <div class="aviso"><?= e($avisoBanco) ?></div>
        <?php endif; ?>
        <?php if ($historico === []): ?>
            <p>Nenhuma analise registrada ainda. Rode o PHPStan acima e abra a tabela <code>analises</code> no HeidiSQL.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>#</th><th>Pasta</th><th>Nivel</th><th>Erros</th><th>Data</th></tr></thead>
                <tbody>
                <?php foreach ($historico as $linha): ?>
                    <tr>
                        <td><?= e($linha['id']) ?></td>
                        <td><?= e($linha['pasta']) ?></td>
                        <td><?= e($linha['nivel']) ?></td>
                        <td><?= e($linha['total_erros']) ?></td>
                        <td><?= e($linha['criada_em']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
