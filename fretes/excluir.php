<?php
require __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $pdo->beginTransaction();
    try {
        $liberar = $pdo->prepare('UPDATE produtos SET frete_id = NULL WHERE frete_id = ?');
        $liberar->execute([$id]);

        $del = $pdo->prepare('DELETE FROM fretes WHERE id = ?');
        $del->execute([$id]);

        $pdo->commit();
        header('Location: listar.php?msg=excluido');
        exit;
    } catch (Throwable $e) {
        $pdo->rollBack();
    }
}

header('Location: listar.php');
exit;
