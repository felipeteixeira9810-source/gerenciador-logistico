<?php
require __DIR__ . '/../config/database.php';

$tituloPagina = 'Fretes';

$fretes = $pdo->query('
    SELECT
        f.id,
        f.data_frete,
        t.nome AS transportador,
        o.cidade AS origem_cidade,
        o.uf AS origem_uf,
        d.cidade AS destino_cidade,
        d.uf AS destino_uf,
        COUNT(p.id) AS qtd_produtos,
        COALESCE(SUM(p.peso), 0) AS peso_total,
        COALESCE(SUM(p.valor), 0) AS valor_total
    FROM fretes f
    JOIN transportadores t ON t.id = f.transportador_id
    JOIN enderecos o ON o.id = f.origem_id
    JOIN enderecos d ON d.id = f.destino_id
    LEFT JOIN produtos p ON p.frete_id = f.id
    GROUP BY f.id
    ORDER BY f.data_frete DESC, f.id DESC
')->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="h4 mb-0">Fretes</h1>
  <a href="form.php" class="btn btn-primary">Gerar novo frete</a>
</div>

<div class="card card-body shadow-sm mb-4">
  <form method="get" action="relatorio.php" class="row g-2 align-items-end">
    <div class="col-sm-5 col-md-3">
      <label for="data" class="form-label mb-1 small fw-semibold">Relatório de fretes do dia</label>
      <input type="date" id="data" name="data" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-sm btn-outline-primary">Baixar relatório (CSV)</button>
    </div>
  </form>
</div>

<?php if (($_GET['msg'] ?? '') === 'salvo'): ?>
  <div class="alert alert-success">Frete salvo com sucesso.</div>
<?php elseif (($_GET['msg'] ?? '') === 'excluido'): ?>
  <div class="alert alert-success">Frete excluído e produtos liberados com sucesso.</div>
<?php endif; ?>

<?php if (!$fretes): ?>
  <div class="card card-body text-center text-muted py-5">
    Nenhum frete gerado ainda. Cadastre produtos, endereços e transportadores e clique em "Gerar novo frete".
  </div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Data</th>
          <th>Transportador</th>
          <th>Rota</th>
          <th class="text-end">Produtos</th>
          <th class="text-end">Peso total (kg)</th>
          <th class="text-end">Valor total (R$)</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($fretes as $f): ?>
          <tr>
            <td class="tabular-nums text-muted"><?= (int)$f['id'] ?></td>
            <td class="tabular-nums"><?= date('d/m/Y', strtotime($f['data_frete'])) ?></td>
            <td><?= e($f['transportador']) ?></td>
            <td>
              <?= e($f['origem_cidade']) ?>/<?= e($f['origem_uf']) ?>
              &rarr;
              <?= e($f['destino_cidade']) ?>/<?= e($f['destino_uf']) ?>
            </td>
            <td class="text-end tabular-nums"><?= (int)$f['qtd_produtos'] ?></td>
            <td class="text-end tabular-nums"><?= number_format((float)$f['peso_total'], 2, ',', '.') ?></td>
            <td class="text-end tabular-nums"><?= number_format((float)$f['valor_total'], 2, ',', '.') ?></td>
            <td class="text-end text-nowrap">
              <a href="detalhes.php?id=<?= (int)$f['id'] ?>" class="btn btn-sm btn-outline-secondary">Detalhes</a>
              <a href="form.php?id=<?= (int)$f['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
              <form method="post" action="excluir.php" class="d-inline"
                    onsubmit="return confirm('Excluir o frete #<?= (int)$f['id'] ?>? Os produtos voltarão a ficar disponíveis.');">
                <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
