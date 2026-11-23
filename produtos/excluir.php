<?php
require __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare('SELECT frete_id FROM produtos WHERE id = ?');
    $stmt->execute([$id]);
    $freteId = $stmt->fetchColumn();

    if ($freteId) {
        header('Location: listar.php?erro=vinculado');
        exit;
    }

    $del = $pdo->prepare('DELETE FROM produtos WHERE id = ?');
    $del->execute([$id]);
    header('Location: listar.php?msg=excluido');
    exit;
}

header('Location: listar.php');
exit;
