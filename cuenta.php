<?php
require __DIR__.'/inc.php';
$msg = '';
if (isset($_GET['salir'])) { unset($_SESSION['m']); header('Location: cuenta.php'); exit; }
if (empty($_SESSION['ct'])) $_SESSION['ct'] = bin2hex(random_bytes(16));
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['ct'], $_POST['ct'] ?? '')) {
  $a = $_POST['a'] ?? ''; $em = strtolower(trim($_POST['email'] ?? '')); $pw = $_POST['p'] ?? '';
  if ($a === 'reg') {
    $nm = trim($_POST['name'] ?? '');
    $ex = $db->prepare("SELECT 1 FROM members WHERE email=?"); $ex->execute([$em]);
    if ($nm === '' || !filter_var($em, FILTER_VALIDATE_EMAIL)) $msg = 'Escribe tu nombre y un correo válido.';
    elseif (strlen($pw) < 8) $msg = 'La contraseña debe tener mínimo 8 caracteres.';
    elseif ($ex->fetchColumn()) $msg = 'Ese correo ya está registrado.';
    else {
      $db->prepare("INSERT INTO members(name,email,hash,last_note) VALUES(?,?,?,(SELECT IFNULL(MAX(id),0) FROM notes))")->execute([$nm, $em, password_hash($pw, PASSWORD_DEFAULT)]);
      session_regenerate_id(true); $_SESSION['m'] = (int)$db->lastInsertId(); header('Location: cuenta.php'); exit;
    }
  } elseif ($a === 'login') {
    $s = $db->prepare("SELECT * FROM members WHERE email=?"); $s->execute([$em]); $r = $s->fetch();
    if ($r && password_verify($pw, $r['hash'])) { session_regenerate_id(true); $_SESSION['m'] = (int)$r['id']; header('Location: cuenta.php'); exit; }
    $msg = 'Correo o contraseña incorrectos.';
  }
}
$mem = null;
if (!empty($_SESSION['m'])) { $s = $db->prepare("SELECT * FROM members WHERE id=?"); $s->execute([$_SESSION['m']]); $mem = $s->fetch(); }
$notes = [];
if ($mem) {
  $notes = $db->query("SELECT * FROM notes ORDER BY id DESC LIMIT 20")->fetchAll();
  if ($notes) $db->prepare("UPDATE members SET last_note=? WHERE id=?")->execute([$notes[0]['id'], $mem['id']]);
}
?><!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Mi cuenta · Nuevo Amanecer</title>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<style>.box2{background:var(--panel);padding:28px;border-top:4px solid var(--amarillo);max-width:460px;margin:0 auto 24px}
.box2 input{width:100%;box-sizing:border-box;padding:12px;margin:6px 0 14px;background:#0b0b0b;color:#fff;border:1px solid #333;font:inherit}
.box2 button{border:0;cursor:pointer;font-family:inherit}.msg{color:var(--amarillo);text-align:center;font-weight:600}
.n{border-left:3px solid var(--dorado);padding:10px 14px;margin-bottom:12px;background:var(--panel)}.n.new{border-color:var(--amarillo)}.n small{color:var(--suave)}</style><link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#0b0b0b">
<link rel="apple-touch-icon" href="icon-192.png">
<script src="app.js" defer></script>
</head><body>
<nav><a href="index.php"><img src="logo.jpg" alt="Logo MNA"></a><div><a href="index.php">Inicio</a><a href="historia.html">Historia</a></div></nav>
<section>
<?php if ($msg): ?><p class="msg"><?=e($msg)?></p><?php endif; ?>
<?php if ($mem): ?>
  <p class="eyebrow">Hola, <?=e($mem['name'])?></p><h2>Avisos 🔔</h2>
  <?php foreach ($notes as $n): ?><div class="n <?= $n['id'] > $mem['last_note'] ? 'new' : '' ?>"><?=e($n['msg'])?><br><small><?=e($n['created'])?><?= $n['id'] > $mem['last_note'] ? ' · nuevo' : '' ?></small></div><?php endforeach; ?>
  <?php if (!$notes): ?><p style="color:var(--suave)">Aún no hay avisos.</p><?php endif; ?>
  <script>window.MNA={key:"<?=e(vapid_pub())?>",ct:"<?=e($_SESSION['ct'])?>"};</script>
  <div class="box2"><h3 style="color:#fff;font-size:1.6rem;margin-bottom:10px">Notificaciones en tu celular</h3>
    <p style="color:var(--suave);margin-bottom:14px">Instala la app y activa los avisos para enterarte de los eventos nuevos.</p>
    <button class="btn instalar" hidden>📲 Instalar app</button> <button class="btn" id="activar">🔔 Activar avisos</button>
    <p id="pstate" class="msg" style="margin-top:12px"></p>
    <p style="color:var(--suave);font-size:.8rem">iPhone: abre esta página en Safari, toca Compartir → Añadir a pantalla de inicio, abre la app desde su ícono y entonces activa los avisos (iOS 16.4 o más).</p></div>
  <p><a class="btn o" href="?salir=1">Cerrar sesión</a></p>
<?php else: ?>
  <p class="eyebrow">Comunidad</p><h2>Mi cuenta</h2>
  <div class="box2"><h3 style="color:#fff;font-size:1.6rem;margin-bottom:10px">Entrar</h3>
    <form method="post"><input type="hidden" name="ct" value="<?=e($_SESSION['ct'])?>"><input type="hidden" name="a" value="login">
    <input name="email" type="email" placeholder="Correo" required><input name="p" type="password" placeholder="Contraseña" required><button class="btn">Entrar</button></form></div>
  <div class="box2"><h3 style="color:#fff;font-size:1.6rem;margin-bottom:10px">Crear cuenta</h3>
    <form method="post"><input type="hidden" name="ct" value="<?=e($_SESSION['ct'])?>"><input type="hidden" name="a" value="reg">
    <input name="name" placeholder="Nombre" required><input name="email" type="email" placeholder="Correo" required><input name="p" type="password" placeholder="Contraseña (mínimo 8 caracteres)" required><button class="btn">Registrarme</button></form></div>
<?php endif; ?>
</section></body></html>
