<?php
require __DIR__ . '/../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$frete = [
    'data_frete'       => date('Y-m-d'),
    'transportador_id' => '',
    'origem_id'        => '',
    'destino_id'       => '',
    'observacoes'      => '',
];
$produtosSelecionados = [];
$erros = [];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM fretes WHERE id = ?');
    $stmt->execute([$id]);
    $encontrado = $stmt->fetch();
    if ($encontrado) {
        $frete = $encontrado;
        $stmtProd = $pdo->prepare('SELECT id FROM produtos WHERE frete_id = ? ORDER BY id');
        $stmtProd->execute([$id]);
        $produtosSelecionados = $stmtProd->fetchAll(PDO::FETCH_COLUMN);
    } else {
        header('Location: listar.php');
        exit;
    }
}

$transportadores = $pdo->query('SELECT id, nome, placa FROM transportadores ORDER BY nome')->fetchAll();
$origens = $pdo->query("SELECT id, logradouro, cidade, uf FROM enderecos WHERE tipo IN ('origem', 'ambos') ORDER BY cidade, logradouro")->fetchAll();
$destinos = $pdo->query("SELECT id, logradouro, cidade, uf FROM enderecos WHERE tipo IN ('destino', 'ambos') ORDER BY cidade, logradouro")->fetchAll();

// Carrega produtos livres ou já vinculados a este frete (em caso de edição)
$stmtDisponiveis = $pdo->prepare('SELECT id, descricao, peso, valor FROM produtos WHERE frete_id IS NULL OR frete_id = ? ORDER BY descricao');
$stmtDisponiveis->execute([$id]);
$produtosDisponiveis = $stmtDisponiveis->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $frete = [
        'data_frete'       => $_POST['data_frete'] ?? '',
        'transportador_id' => (int)($_POST['transportador_id'] ?? 0),
        'origem_id'        => (int)($_POST['origem_id'] ?? 0),
        'destino_id'       => (int)($_POST['destino_id'] ?? 0),
        'observacoes'      => trim($_POST['observacoes'] ?? ''),
    ];

    // Filtra IDs de produtos válidos e remove duplicados
    $produtosSelecionados = array_values(array_unique(array_filter(
        array_map('intval', $_POST['produtos'] ?? []),
        static fn(int $pid): bool => $pid > 0
    )));

    if ($frete['data_frete'] === '') {
        $erros[] = 'Informe a data do frete.';
    }
    if ($frete['transportador_id'] <= 0) {
        $erros[] = 'Selecione um transportador.';
    }
    if ($frete['origem_id'] <= 0) {
        $erros[] = 'Selecione o endereço de origem.';
    }
    if ($frete['destino_id'] <= 0) {
        $erros[] = 'Selecione o endereço de destino.';
    }
    if ($frete['origem_id'] > 0 && $frete['origem_id'] === $frete['destino_id']) {
        $erros[] = 'O endereço de origem e o de destino devem ser diferentes.';
    }
    if (!$produtosSelecionados) {
        $erros[] = 'Adicione pelo menos um produto ao frete.';
    }

    if (!$erros) {
        $pdo->beginTransaction();
        try {
            if ($id > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE fretes SET data_frete = ?, transportador_id = ?, origem_id = ?, destino_id = ?, observacoes = ? WHERE id = ?'
                );
                $stmt->execute([
                    $frete['data_frete'],
                    $frete['transportador_id'],
                    $frete['origem_id'],
                    $frete['destino_id'],
                    $frete['observacoes'] ?: null,
                    $id,
                ]);
                $freteId = $id;

                // Libera os produtos anteriormente vinculados a este frete
                $limpar = $pdo->prepare('UPDATE produtos SET frete_id = NULL WHERE frete_id = ?');
                $limpar->execute([$freteId]);
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO fretes (data_frete, transportador_id, origem_id, destino_id, observacoes) VALUES (?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $frete['data_frete'],
                    $frete['transportador_id'],
                    $frete['origem_id'],
                    $frete['destino_id'],
                    $frete['observacoes'] ?: null,
                ]);
                $freteId = (int)$pdo->lastInsertId();
            }

            // Vincula os produtos selecionados ao frete (relação 1 para muitos)
            $vincular = $pdo->prepare('UPDATE produtos SET frete_id = ? WHERE id = ?');
            foreach ($produtosSelecionados as $produtoId) {
                $vincular->execute([$freteId, $produtoId]);
            }

            $pdo->commit();
            header('Location: listar.php?msg=salvo');
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $erros[] = 'Erro ao salvar o frete. Tente novamente.';
        }
    }
}

$tituloPagina = $id > 0 ? "Editar frete #{$id}" : 'Gerar novo frete';
$scriptsExtras = [BASE_URL . '/assets/js/fretes.js'];
require __DIR__ . '/../includes/header.php';
?>
<h1 class="h4 mb-3"><?= e($tituloPagina) ?></h1>

<?php if (!$transportadores || !$origens || !$destinos || !$produtosDisponiveis): ?>
  <div class="alert alert-warning">
    Para gerar um frete, você precisa ter cadastrado pelo menos:
    <ul class="mb-0 mt-1">
      <?php if (!$transportadores): ?><li>Um <a href="<?= BASE_URL ?>/transportadores/form.php">transportador</a></li><?php endif; ?>
      <?php if (!$origens): ?><li>Um <a href="<?= BASE_URL ?>/enderecos/form.php">endereço de origem</a></li><?php endif; ?>
      <?php if (!$destinos): ?><li>Um <a href="<?= BASE_URL ?>/enderecos/form.php">endereço de destino</a></li><?php endif; ?>
      <?php if (!$produtosDisponiveis): ?><li>Um <a href="<?= BASE_URL ?>/produtos/form.php">produto disponível</a></li><?php endif; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($erros): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($erros as $erro): ?>
        <li><?= e($erro) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" class="card card-body shadow-sm" style="max-width: 780px;">
  <div class="row g-3">
    <div class="col-md-4">
      <label for="data_frete" class="form-label">Data do frete *</label>
      <input type="date" id="data_frete" name="data_frete" class="form-control"
             value="<?= e((string)$frete['data_frete']) ?>" required>
    </div>
    <div class="col-md-8">
      <label for="transportador_id" class="form-label">Transportador *</label>
      <select id="transportador_id" name="transportador_id" class="form-select" required>
        <option value="">Selecione...</option>
        <?php foreach ($transportadores as $t): ?>
          <option value="<?= (int)$t['id'] ?>" <?= (int)$frete['transportador_id'] === (int)$t['id'] ? 'selected' : '' ?>>
            <?= e($t['nome']) ?><?= $t['placa'] ? ' (' . e($t['placa']) . ')' : '' ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label for="origem_id" class="form-label">Endereço de origem *</label>
      <select id="origem_id" name="origem_id" class="form-select" required>
        <option value="">Selecione...</option>
        <?php foreach ($origens as $o): ?>
          <option value="<?= (int)$o['id'] ?>" <?= (int)$frete['origem_id'] === (int)$o['id'] ? 'selected' : '' ?>>
            <?= e($o['cidade']) ?>/<?= e($o['uf']) ?> - <?= e($o['logradouro']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label for="destino_id" class="form-label">Endereço de destino *</label>
      <select id="destino_id" name="destino_id" class="form-select" required>
        <option value="">Selecione...</option>
        <?php foreach ($destinos as $d): ?>
          <option value="<?= (int)$d['id'] ?>" <?= (int)$frete['destino_id'] === (int)$d['id'] ? 'selected' : '' ?>>
            <?= e($d['cidade']) ?>/<?= e($d['uf']) ?> - <?= e($d['logradouro']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12">
      <label for="observacoes" class="form-label">Observações</label>
      <input type="text" id="observacoes" name="observacoes" class="form-control"
             value="<?= e((string)$frete['observacoes']) ?>" maxlength="255">
    </div>
  </div>

  <hr class="my-4">

  <div class="d-flex justify-content-between align-items-center mb-2">
    <div>
      <h2 class="h6 mb-0">Produtos do frete *</h2>
      <small class="text-muted">Um transportador pode levar vários produtos no mesmo frete.</small>
    </div>
    <button type="button" id="btn-add-produto" class="btn btn-sm btn-outline-primary">+ Adicionar produto</button>
  </div>

  <div id="lista-produtos" class="d-flex flex-column gap-2">
    <?php foreach ($produtosSelecionados as $pidSelecionado): ?>
      <div class="linha-produto input-group">
        <select name="produtos[]" class="form-select" required>
          <option value="">Selecione um produto...</option>
          <?php foreach ($produtosDisponiveis as $p): ?>
            <option value="<?= (int)$p['id'] ?>" <?= (int)$pidSelecionado === (int)$p['id'] ? 'selected' : '' ?>>
              <?= e($p['descricao']) ?> — <?= number_format((float)$p['peso'], 2, ',', '.') ?> kg — R$ <?= number_format((float)$p['valor'], 2, ',', '.') ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="button" class="btn btn-outline-danger btn-remover">Remover</button>
      </div>
    <?php endforeach; ?>
  </div>

  <template id="modelo-produto">
    <div class="linha-produto input-group">
      <select name="produtos[]" class="form-select" required>
        <option value="">Selecione um produto...</option>
        <?php foreach ($produtosDisponiveis as $p): ?>
          <option value="<?= (int)$p['id'] ?>">
            <?= e($p['descricao']) ?> — <?= number_format((float)$p['peso'], 2, ',', '.') ?> kg — R$ <?= number_format((float)$p['valor'], 2, ',', '.') ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="button" class="btn btn-outline-danger btn-remover">Remover</button>
    </div>
  </template>

  <div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary">Salvar frete</button>
    <a href="listar.php" class="btn btn-outline-secondary">Cancelar</a>
  </div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
