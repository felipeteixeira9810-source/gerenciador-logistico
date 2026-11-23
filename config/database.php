<?php
declare(strict_types=1);

$host = 'localhost';
$dbname = 'logistica';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Erro ao conectar ao banco de dados. Verifique se o MySQL está ativo no XAMPP e se o banco "logistica" foi criado.';
    exit;
}

/**
 * Caminho base do projeto no XAMPP (htdocs/gerenciador-logistico).
 * Altere caso mude o nome da pasta.
 */
define('BASE_URL', '/gerenciador-logistico');

/**
 * Escapa texto para exibição segura em HTML.
 */
function e(?string $valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}
