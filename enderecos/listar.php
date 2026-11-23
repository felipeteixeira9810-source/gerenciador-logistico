<?php
require __DIR__ . '/../config/database.php';

$tituloPagina = 'Endereços';
$enderecos = $pdo->query('SELECT * FROM enderecos ORDER BY id DESC')->fetchAll();

$rotuloTipo = [
    'origem'  => ['Origem', 'text-bg-info'],
    'destino' => ['Destino', 'text-bg-warning'],
    'ambos'   => ['Origem e Destino', 'text-bg-secondary'],
];

require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Endereços</h1>
  <a href="form.php" class="btn btn-primary">Novo endereço</a>
</div>

<?php if (($_GET['msg'] ?? '') === 'salvo'): ?>
  <div class="alert alert-success">Endereço salvo com sucesso.</div>
<?php elseif (($_GET['msg'] ?? '') === 'excluido'): ?>
  <div class="alert alert-success">Endereço excluído com sucesso.</div>
<?php elseif (($_GET['erro'] ?? '') === 'vinculado'): ?>
  <div class="alert alert-warning">Não foi possível excluir: este endereço está em uso em um ou mais fretes.</div>
<?php endif; ?>

<?php if (!$enderecos): ?>
  <div class="card card-body text-center text-muted py-5">
    Nenhum endereço cadastrado ainda.
  </div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover bg-white shadow-sm align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Tipo</th>
          <th>Endereço</th>
          <th>Cidade / UF</th>
          <th>CEP</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($enderecos as $end): ?>
          <?php [$textoTipo, $classeTipo] = $rotuloTipo[$end['tipo']] ?? ['Ambos', 'text-bg-secondary']; ?>
          <tr>
            <td class="tabular-nums text-muted"><?= (int)$end['id'] ?></td>
            <td><span class="badge <?= $classeTipo ?>"><?= e($textoTipo) ?></span></td>
            <td>
              <?= e($end['logradouro']) ?><?= $end['numero'] ? ', ' . e($end['numero']) : '' ?>
              <?php if ($end['bairro']): ?>
                <div class="small text-muted"><?= e($end['bairro']) ?></div>
              <?php endif; ?>
            </td>
            <td><?= e($end['cidade']) ?> / <?= e($end['uf']) ?></td>
            <td class="tabular-nums"><?= e($end['cep']) ?></td>
            <td class="text-end text-nowrap">
              <a href="form.php?id=<?= (int)$end['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
              <form method="post" action="excluir.php" class="d-inline"
                    onsubmit="return confirm('Excluir o endereço <?= e($end['logradouro']) ?>?');">
                <input type="hidden" name="id" value="<?= (int)$end['id'] ?>">
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
