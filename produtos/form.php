<?php
require __DIR__ . '/../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$produto = ['descricao' => '', 'peso' => '', 'volume' => '', 'valor' => ''];
$erros = [];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM produtos WHERE id = ?');
    $stmt->execute([$id]);
    $encontrado = $stmt->fetch();
    if ($encontrado) {
        $produto = $encontrado;
    } else {
        header('Location: listar.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto = [
        'descricao' => trim($_POST['descricao'] ?? ''),
        'peso'      => trim($_POST['peso'] ?? ''),
        'volume'    => trim($_POST['volume'] ?? ''),
        'valor'     => trim($_POST['valor'] ?? ''),
    ];

    if ($produto['descricao'] === '') {
        $erros[] = 'Informe a descrição do produto.';
    }
    foreach (['peso' => 'Peso', 'volume' => 'Volume', 'valor' => 'Valor'] as $campo => $rotulo) {
        if ($produto[$campo] !== '' && (!is_numeric($produto[$campo]) || (float)$produto[$campo] < 0)) {
            $erros[] = "{$rotulo} deve ser um número válido maior ou igual a zero.";
        }
    }

    if (!$erros) {
        $dados = [
            $produto['descricao'],
            $produto['peso'] === '' ? 0 : (float)$produto['peso'],
            $produto['volume'] === '' ? 0 : (float)$produto['volume'],
            $produto['valor'] === '' ? 0 : (float)$produto['valor'],
        ];

        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE produtos SET descricao = ?, peso = ?, volume = ?, valor = ? WHERE id = ?');
            $stmt->execute([...$dados, $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO produtos (descricao, peso, volume, valor) VALUES (?, ?, ?, ?)');
            $stmt->execute($dados);
        }

        header('Location: listar.php?msg=salvo');
        exit;
    }
}

$tituloPagina = $id > 0 ? 'Editar produto' : 'Novo produto';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="h4 mb-3"><?= e($tituloPagina) ?></h1>

<?php if ($erros): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($erros as $erro): ?>
        <li><?= e($erro) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" class="card card-body shadow-sm" style="max-width: 640px;">
  <div class="mb-3">
    <label for="descricao" class="form-label">Descrição *</label>
    <input type="text" id="descricao" name="descricao" class="form-control"
           value="<?= e((string)$produto['descricao']) ?>" required maxlength="150">
  </div>
  <div class="row g-3">
    <div class="col-md-4">
      <label for="peso" class="form-label">Peso (kg)</label>
      <input type="number" step="0.01" min="0" id="peso" name="peso" class="form-control"
             value="<?= e((string)$produto['peso']) ?>">
    </div>
    <div class="col-md-4">
      <label for="volume" class="form-label">Volume (m³)</label>
      <input type="number" step="0.001" min="0" id="volume" name="volume" class="form-control"
             value="<?= e((string)$produto['volume']) ?>">
    </div>
    <div class="col-md-4">
      <label for="valor" class="form-label">Valor (R$)</label>
      <input type="number" step="0.01" min="0" id="valor" name="valor" class="form-control"
             value="<?= e((string)$produto['valor']) ?>">
    </div>
  </div>
  <div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="listar.php" class="btn btn-outline-secondary">Cancelar</a>
  </div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
