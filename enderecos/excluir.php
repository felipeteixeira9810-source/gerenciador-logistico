<?php
require __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        $stmt = $pdo->prepare('DELETE FROM enderecos WHERE id = ?');
        $stmt->execute([(int)$_POST['id']]);
        header('Location: listar.php?msg=excluido');
        exit;
    } catch (PDOException $e) {
        header('Location: listar.php?erro=vinculado');
        exit;
    }
}

header('Location: listar.php');
exit;
