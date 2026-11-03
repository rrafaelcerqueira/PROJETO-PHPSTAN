# PHPStan na pratica (PHP + Docker + MySQL + HeidiSQL)

Site simples para o trabalho da faculdade. Ele roda o **PHPStan** em duas pastas de codigo
(uma com erros propositais e outra corrigida) e mostra o resultado na tela.
Cada analise tambem e salva no MySQL, para voce abrir no **HeidiSQL**.

## O que e o PHPStan

Ferramenta de **analise estatica**: le o codigo PHP **sem executar** e aponta erros de tipo,
metodos que nao existem, variaveis nao definidas, valores `null` mal tratados etc.
O rigor vai de `level 0` ate `level 9` (configurado em `phpstan.neon`).

## Fluxo demonstrado

1. Codigo PHP em `src/ComErros` (com bugs de proposito).
2. O site chama `vendor/bin/phpstan analyse ... --error-format=json`.
3. O PHP le o JSON e mostra arquivo, linha e mensagem de cada erro.
4. O resultado vai para o MySQL (tabelas `analises` e `erros`).
5. Rodando em `src/Corrigido` (versao corrigida) o resultado fica sem erros.

## Como rodar

```bash
docker compose up -d --build
docker compose exec web composer install
```

Abra http://localhost:8080 , escolha a pasta e o nivel e clique em **Analisar**.

Tambem da para rodar direto no terminal:

```bash
docker compose exec web vendor/bin/phpstan analyse
docker compose exec web vendor/bin/phpstan analyse src/Corrigido --level=9
```

## HeidiSQL

| Campo | Valor |
|---|---|
| Tipo de rede | MariaDB or MySQL (TCP/IP) |
| Nome do host | 127.0.0.1 |
| Usuario | root |
| Senha | root |
| Porta | 3307 |
| Banco | phpstan_demo |

Tabelas: `analises` (uma linha por execucao) e `erros` (uma linha por erro encontrado).

## Estrutura

```
docker-compose.yml   # PHP/Apache + MySQL
Dockerfile
composer.json        # phpstan/phpstan como dependencia de desenvolvimento
phpstan.neon         # configuracao (nivel e pastas)
sql/init.sql         # cria o banco e as tabelas
public/index.php     # o site
src/ComErros/        # codigo com erros propositais
src/Corrigido/       # mesmo codigo, corrigido
src/Infra/           # roda o PHPStan e fala com o banco
```

## Nivel x tipo de erro (para o relatorio)

| Nivel | O que passa a ser verificado |
|---|---|
| 0 | Basico: classes, funcoes e metodos desconhecidos |
| 1 | Variaveis nao definidas |
| 3 | Tipos de retorno e de propriedades |
| 4 | Codigo morto e condicoes sempre verdadeiras |
| 5 | Tipos dos argumentos passados |
| 6 | Tipos faltando (ex.: valores de arrays) |
| 8 | Acesso a valores que podem ser `null` |
