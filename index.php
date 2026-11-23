<?php
require __DIR__ . '/config/database.php';

$tituloPagina = 'Início';

$totais = [
    'produtos'        => (int)$pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn(),
    'enderecos'       => (int)$pdo->query('SELECT COUNT(*) FROM enderecos')->fetchColumn(),
    'transportadores' => (int)$pdo->query('SELECT COUNT(*) FROM transportadores')->fetchColumn(),
    'fretes'          => (int)$pdo->query('SELECT COUNT(*) FROM fretes')->fetchColumn(),
];

require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div>
    <h1 class="h3 mb-1">Painel Logístico</h1>
    <p class="text-muted mb-0">Cadastre produtos, endereços e transportadores para gerar fretes e relatórios diários.</p>
  </div>
  <a href="<?= BASE_URL ?>/fretes/form.php" class="btn btn-primary">Novo frete</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card shadow-sm card-resumo h-100">
      <div class="card-body">
        <div class="text-muted small">Produtos</div>
        <div class="fs-2 fw-bold tabular-nums"><?= $totais['produtos'] ?></div>
        <a href="<?= BASE_URL ?>/produtos/listar.php" class="stretched-link text-decoration-none small">Gerenciar produtos</a>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card shadow-sm card-resumo h-100">
      <div class="card-body">
        <div class="text-muted small">Endereços</div>
        <div class="fs-2 fw-bold tabular-nums"><?= $totais['enderecos'] ?></div>
        <a href="<?= BASE_URL ?>/enderecos/listar.php" class="stretched-link text-decoration-none small">Gerenciar endereços</a>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card shadow-sm card-resumo h-100">
      <div class="card-body">
        <div class="text-muted small">Transportadores</div>
        <div class="fs-2 fw-bold tabular-nums"><?= $totais['transportadores'] ?></div>
        <a href="<?= BASE_URL ?>/transportadores/listar.php" class="stretched-link text-decoration-none small">Gerenciar transportadores</a>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card shadow-sm card-resumo h-100">
      <div class="card-body">
        <div class="text-muted small">Fretes gerados</div>
        <div class="fs-2 fw-bold tabular-nums"><?= $totais['fretes'] ?></div>
        <a href="<?= BASE_URL ?>/fretes/listar.php" class="stretched-link text-decoration-none small">Ver fretes e relatórios</a>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <h2 class="h5 mb-3">Baixar relatório diário de fretes</h2>
    <form method="get" action="<?= BASE_URL ?>/fretes/relatorio.php" class="row g-2 align-items-end">
      <div class="col-sm-5 col-md-4">
        <label for="data_relatorio" class="form-label">Data dos fretes</label>
        <input type="date" id="data_relatorio" name="data" class="form-control" value="<?= date('Y-m-d') ?>" required>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-outline-primary">Baixar CSV</button>
      </div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
