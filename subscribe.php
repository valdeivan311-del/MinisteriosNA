<?php
require __DIR__.'/inc.php';
header('Content-Type: application/json');
$d = json_decode(file_get_contents('php://input'), true) ?: [];
$ep = $d['endpoint'] ?? ''; $host = parse_url($ep, PHP_URL_HOST) ?: '';
// Solo miembros con sesión, token válido y servicios de push conocidos (evita enviar a direcciones arbitrarias)
if (empty($_SESSION['m']) || !hash_equals($_SESSION['ct'] ?? '', $d['t'] ?? '') || strpos($ep,'https://') !== 0
    || !preg_match('/(^|\.)(googleapis\.com|mozilla\.com|push\.apple\.com|windows\.com)$/', $host)) { http_response_code(400); echo '{"ok":false}'; exit; }
$db->prepare("INSERT OR REPLACE INTO push_subs(endpoint,member_id) VALUES(?,?)")->execute([$ep, $_SESSION['m']]);
echo '{"ok":true}';
