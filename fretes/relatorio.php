<?php
require __DIR__ . '/../config/database.php';

$data = $_GET['data'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
    $data = date('Y-m-d');
}

$stmt = $pdo->prepare('
    SELECT
        f.id,
        f.data_frete,
        t.nome AS transportador,
        t.placa AS placa,
        CONCAT(o.cidade, "/", o.uf) AS origem,
        CONCAT(d.cidade, "/", d.uf) AS destino,
        COUNT(p.id) AS qtd_produtos,
        COALESCE(SUM(p.peso), 0) AS peso_total,
        COALESCE(SUM(p.valor), 0) AS valor_total,
        COALESCE(GROUP_CONCAT(p.descricao SEPARATOR " | "), "") AS lista_produtos
    FROM fretes f
    JOIN transportadores t ON t.id = f.transportador_id
    JOIN enderecos o ON o.id = f.origem_id
    JOIN enderecos d ON d.id = f.destino_id
    LEFT JOIN produtos p ON p.frete_id = f.id
    WHERE f.data_frete = ?
    GROUP BY f.id
    ORDER BY f.id ASC
');
$stmt->execute([$data]);
$linhas = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=\"relatorio_fretes_{$data}.csv\"");

$saida = fopen('php://output', 'w');

// BOM UTF-8 para o Excel abrir acentos corretamente
fwrite($saida, "\xEF\xBB\xBF");

fputcsv($saida, [
    'ID Frete',
    'Data',
    'Transportador',
    'Placa',
    'Origem',
    'Destino',
    'Qtd. Produtos',
    'Peso Total (kg)',
    'Valor Total (R$)',
    'Produtos',
], ';');

foreach ($linhas as $l) {
    fputcsv($saida, [
        $l['id'],
        date('d/m/Y', strtotime($l['data_frete'])),
        $l['transportador'],
        $l['placa'] ?? '',
        $l['origem'],
        $l['destino'],
        $l['qtd_produtos'],
        number_format((float)$l['peso_total'], 2, ',', '.'),
        number_format((float)$l['valor_total'], 2, ',', '.'),
        $l['lista_produtos'],
    ], ';');
}

fclose($saida);
exit;
