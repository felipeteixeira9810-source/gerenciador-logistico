<?php
$paginaAtual = $_SERVER['PHP_SELF'] ?? '';
$ativo = static fn(string $trecho): string => str_contains($paginaAtual, $trecho) ? 'active' : '';
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($tituloPagina) ? e($tituloPagina) . ' - ' : '' ?>Gerenciador Logístico</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="<?= BASE_URL ?>/index.php">Gerenciador Logístico</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
            aria-controls="menuPrincipal" aria-expanded="false" aria-label="Alternar navegação">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menuPrincipal">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link <?= $ativo('/index.php') ?>" href="<?= BASE_URL ?>/index.php">Início</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $ativo('/produtos/') ?>" href="<?= BASE_URL ?>/produtos/listar.php">Produtos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $ativo('/enderecos/') ?>" href="<?= BASE_URL ?>/enderecos/listar.php">Endereços</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $ativo('/transportadores/') ?>" href="<?= BASE_URL ?>/transportadores/listar.php">Transportadores</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $ativo('/fretes/') ?>" href="<?= BASE_URL ?>/fretes/listar.php">Fretes</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<main class="container pb-5">
