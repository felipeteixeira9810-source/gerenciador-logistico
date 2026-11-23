<?php
require __DIR__ . '/../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare('
    SELECT
        f.*,
        t.nome AS transportador_nome,
        t.documento AS transportador_documento,
        t.placa AS transportador_placa,
        t.telefone AS transportador_telefone,
        o.logradouro AS origem_logradouro,
        o.numero AS origem_numero,
        o.bairro AS origem_bairro,
        o.cidade AS origem_cidade,
        o.uf AS origem_uf,
        o.cep AS origem_cep,
        d.logradouro AS destino_logradouro,
        d.numero AS destino_numero,
        d.bairro AS destino_bairro,
        d.cidade AS destino_cidade,
        d.uf AS destino_uf,
        d.cep AS destino_cep
    FROM fretes f
    JOIN transportadores t ON t.id = f.transportador_id
    JOIN enderecos o ON o.id = f.origem_id
    JOIN enderecos d ON d.id = f.destino_id
    WHERE f.id = ?
');
$stmt->execute([$id]);
$frete = $stmt->fetch();

if (!$frete) {
    header('Location: listar.php');
    exit;
}

$stmtProdutos = $pdo->prepare('SELECT * FROM produtos WHERE frete_id = ? ORDER BY descricao');
$stmtProdutos->execute([$id]);
$produtos = $stmtProdutos->fetchAll();

$pesoTotal = array_sum(array_column($produtos, 'peso'));
$volumeTotal = array_sum(array_column($produtos, 'volume'));
$valorTotal = array_sum(array_column($produtos, 'valor'));

$tituloPagina = "Frete #{$id}";
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="h4 mb-0">Detalhes do frete #<?= (int)$frete['id'] ?></h1>
  <div class="d-flex gap-2">
    <a href="form.php?id=<?= (int)$frete['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
    <a href="listar.php" class="btn btn-sm btn-outline-secondary">Voltar</a>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h2 class="h6 text-muted">Transportador</h2>
        <p class="fw-semibold mb-1"><?= e($frete['transportador_nome']) ?></p>
        <div class="small text-muted">
          Data: <?= date('d/m/Y', strtotime($frete['data_frete'])) ?><br>
          <?php if ($frete['transportador_documento']): ?>Doc.: <?= e($frete['transportador_documento']) ?><br><?php endif; ?>
          <?php if ($frete['transportador_placa']): ?>Placa: <?= e($frete['transportador_placa']) ?><br><?php endif; ?>
          <?php if ($frete['transportador_telefone']): ?>Tel.: <?= e($frete['transportador_telefone']) ?><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h2 class="h6 text-muted">Origem</h2>
        <p class="fw-semibold mb-1">
          <?= e($frete['origem_logradouro']) ?><?= $frete['origem_numero'] ? ', ' . e($frete['origem_numero']) : '' ?>
        </p>
        <div class="small text-muted">
          <?php if ($frete['origem_bairro']): ?><?= e($frete['origem_bairro']) ?><br><?php endif; ?>
          <?= e($frete['origem_cidade']) ?> / <?= e($frete['origem_uf']) ?><br>
          <?php if ($frete['origem_cep']): ?>CEP: <?= e($frete['origem_cep']) ?><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h2 class="h6 text-muted">Destino</h2>
        <p class="fw-semibold mb-1">
          <?= e($frete['destino_logradouro']) ?><?= $frete['destino_numero'] ? ', ' . e($frete['destino_numero']) : '' ?>
        </p>
        <div class="small text-muted">
          <?php if ($frete['destino_bairro']): ?><?= e($frete['destino_bairro']) ?><br><?php endif; ?>
          <?= e($frete['destino_cidade']) ?> / <?= e($frete['destino_uf']) ?><br>
          <?php if ($frete['destino_cep']): ?>CEP: <?= e($frete['destino_cep']) ?><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if ($frete['observacoes']): ?>
  <div class="alert alert-light border mb-4">
    <strong>Observações:</strong> <?= e($frete['observacoes']) ?>
  </div>
<?php endif; ?>

<h2 class="h5 mb-2">Produtos transportados (<?= count($produtos) ?>)</h2>
<div class="table-responsive">
  <table class="table bg-white shadow-sm align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>Descrição</th>
        <th class="text-end">Peso (kg)</th>
        <th class="text-end">Volume (m³)</th>
        <th class="text-end">Valor (R$)</th>
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
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot class="table-light fw-semibold">
      <tr>
        <td colspan="2">Totais</td>
        <td class="text-end tabular-nums"><?= number_format((float)$pesoTotal, 2, ',', '.') ?></td>
        <td class="text-end tabular-nums"><?= number_format((float)$volumeTotal, 3, ',', '.') ?></td>
        <td class="text-end tabular-nums"><?= number_format((float)$valorTotal, 2, ',', '.') ?></td>
      </tr>
    </tfoot>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
