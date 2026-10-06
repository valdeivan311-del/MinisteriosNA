<?php
require __DIR__.'/inc.php';
header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');
$m = $db->query("SELECT msg FROM notes ORDER BY id DESC LIMIT 1")->fetchColumn();
echo json_encode(['msg' => $m ?: 'Hay novedades en Nuevo Amanecer']);
