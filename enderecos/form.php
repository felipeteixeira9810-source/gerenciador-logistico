<?php
require __DIR__ . '/../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$endereco = [
    'tipo'       => 'ambos',
    'logradouro' => '',
    'numero'     => '',
    'bairro'     => '',
    'cidade'     => '',
    'uf'         => '',
    'cep'        => '',
];
$erros = [];
$tiposValidos = ['origem', 'destino', 'ambos'];

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM enderecos WHERE id = ?');
    $stmt->execute([$id]);
    $encontrado = $stmt->fetch();
    if ($encontrado) {
        $endereco = $encontrado;
    } else {
        header('Location: listar.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $endereco = [
        'tipo'       => $_POST['tipo'] ?? 'ambos',
        'logradouro' => trim($_POST['logradouro'] ?? ''),
        'numero'     => trim($_POST['numero'] ?? ''),
        'bairro'     => trim($_POST['bairro'] ?? ''),
        'cidade'     => trim($_POST['cidade'] ?? ''),
        'uf'         => strtoupper(trim($_POST['uf'] ?? '')),
        'cep'        => trim($_POST['cep'] ?? ''),
    ];

    if (!in_array($endereco['tipo'], $tiposValidos, true)) {
        $erros[] = 'Selecione um tipo de endereço válido.';
    }
    if ($endereco['logradouro'] === '') {
        $erros[] = 'Informe o logradouro (rua/avenida).';
    }
    if ($endereco['cidade'] === '') {
        $erros[] = 'Informe a cidade.';
    }
    if (strlen($endereco['uf']) !== 2) {
        $erros[] = 'Informe a UF com 2 letras (ex.: PR, SP).';
    }

    if (!$erros) {
        $dados = [
            $endereco['tipo'],
            $endereco['logradouro'],
            $endereco['numero'] ?: null,
            $endereco['bairro'] ?: null,
            $endereco['cidade'],
            $endereco['uf'],
            $endereco['cep'] ?: null,
        ];

        if ($id > 0) {
            $stmt = $pdo->prepare(
                'UPDATE enderecos SET tipo = ?, logradouro = ?, numero = ?, bairro = ?, cidade = ?, uf = ?, cep = ? WHERE id = ?'
            );
            $stmt->execute([...$dados, $id]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO enderecos (tipo, logradouro, numero, bairro, cidade, uf, cep) VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute($dados);
        }

        header('Location: listar.php?msg=salvo');
        exit;
    }
}

$tituloPagina = $id > 0 ? 'Editar endereço' : 'Novo endereço';
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

<form method="post" class="card card-body shadow-sm" style="max-width: 720px;">
  <div class="row g-3">
    <div class="col-md-4">
      <label for="tipo" class="form-label">Tipo *</label>
      <select id="tipo" name="tipo" class="form-select" required>
        <option value="origem" <?= $endereco['tipo'] === 'origem' ? 'selected' : '' ?>>Origem</option>
        <option value="destino" <?= $endereco['tipo'] === 'destino' ? 'selected' : '' ?>>Destino</option>
        <option value="ambos" <?= $endereco['tipo'] === 'ambos' ? 'selected' : '' ?>>Origem e Destino</option>
      </select>
    </div>
    <div class="col-md-8">
      <label for="logradouro" class="form-label">Logradouro *</label>
      <input type="text" id="logradouro" name="logradouro" class="form-control"
             value="<?= e((string)$endereco['logradouro']) ?>" required maxlength="150">
    </div>
    <div class="col-md-3">
      <label for="numero" class="form-label">Número</label>
      <input type="text" id="numero" name="numero" class="form-control"
             value="<?= e((string)$endereco['numero']) ?>" maxlength="20">
    </div>
    <div class="col-md-5">
      <label for="bairro" class="form-label">Bairro</label>
      <input type="text" id="bairro" name="bairro" class="form-control"
             value="<?= e((string)$endereco['bairro']) ?>" maxlength="80">
    </div>
    <div class="col-md-4">
      <label for="cep" class="form-label">CEP</label>
      <input type="text" id="cep" name="cep" class="form-control" placeholder="00000-000"
             value="<?= e((string)$endereco['cep']) ?>" maxlength="9">
    </div>
    <div class="col-md-9">
      <label for="cidade" class="form-label">Cidade *</label>
      <input type="text" id="cidade" name="cidade" class="form-control"
             value="<?= e((string)$endereco['cidade']) ?>" required maxlength="80">
    </div>
    <div class="col-md-3">
      <label for="uf" class="form-label">UF *</label>
      <input type="text" id="uf" name="uf" class="form-control text-uppercase" placeholder="PR"
             value="<?= e((string)$endereco['uf']) ?>" required maxlength="2">
    </div>
  </div>
  <div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="listar.php" class="btn btn-outline-secondary">Cancelar</a>
  </div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
