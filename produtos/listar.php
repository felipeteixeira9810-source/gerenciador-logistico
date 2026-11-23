<?php
require __DIR__ . '/../config/database.php';

$tituloPagina = 'Produtos';
$produtos = $pdo->query('SELECT * FROM produtos ORDER BY id DESC')->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Produtos</h1>
  <a href="form.php" class="btn btn-primary">Novo produto</a>
</div>

<?php if (($_GET['msg'] ?? '') === 'salvo'): ?>
  <div class="alert alert-success">Produto salvo com sucesso.</div>
<?php elseif (($_GET['msg'] ?? '') === 'excluido'): ?>
  <div class="alert alert-success">Produto excluído com sucesso.</div>
<?php elseif (($_GET['erro'] ?? '') === 'vinculado'): ?>
  <div class="alert alert-warning">Não foi possível excluir: este produto está vinculado a um frete.</div>
<?php endif; ?>

<?php if (!$produtos): ?>
  <div class="card card-body text-center text-muted py-5">
    Nenhum produto cadastrado ainda.
  </div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Descrição</th>
          <th class="text-end">Peso (kg)</th>
          <th class="text-end">Volume (m³)</th>
          <th class="text-end">Valor (R$)</th>
          <th>Status</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($produtos as $p): ?>
          <tr>
            <td class="tabular-nums text-muted"><?= (int)$p['id'] ?></td>
            <td><?= e($p['descricao']) ?></td>
            <td class="text-end tabular-nums"><?= number_format((float)$p['peso'], 2, ',', '.') ?></td>
            <td class="text-end tabular-nums"><?= number_format((float)$p['volume'], 3, ',', '.') ?></td>
            <td class="text-end tabular-nums"><?= number_format((float)$p['valor'], 2, ',', '.') ?></td>
            <td>
              <?php if ($p['frete_id']): ?>
                <span class="badge text-bg-secondary">Frete #<?= (int)$p['frete_id'] ?></span>
              <?php else: ?>
                <span class="badge text-bg-success">Disponível</span>
              <?php endif; ?>
            </td>
            <td class="text-end text-nowrap">
              <a href="form.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
              <form method="post" action="excluir.php" class="d-inline"
                    onsubmit="return confirm('Excluir o produto <?= e($p['descricao']) ?>?');">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
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
