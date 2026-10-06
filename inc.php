<?php
// Configuración común: base de datos, sesión y funciones de ayuda
ini_set('session.gc_maxlifetime', 2592000);
session_set_cookie_params(['lifetime'=>2592000,'httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS'])]);
session_start();
define('BASE', __DIR__);
if (!is_dir(BASE.'/data')) mkdir(BASE.'/data', 0755, true);
$db = new PDO('sqlite:'.BASE.'/data/mna.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE IF NOT EXISTS users(id INTEGER PRIMARY KEY, user TEXT UNIQUE, hash TEXT);
CREATE TABLE IF NOT EXISTS events(id INTEGER PRIMARY KEY, title TEXT, date TEXT, descr TEXT, img TEXT);
CREATE TABLE IF NOT EXISTS gallery(id INTEGER PRIMARY KEY, grp TEXT, caption TEXT, file TEXT);
CREATE TABLE IF NOT EXISTS reels(id INTEGER PRIMARY KEY, url TEXT, file TEXT);
CREATE TABLE IF NOT EXISTS settings(k TEXT PRIMARY KEY, v TEXT);
CREATE TABLE IF NOT EXISTS members(id INTEGER PRIMARY KEY, name TEXT, email TEXT UNIQUE, hash TEXT, last_note INTEGER DEFAULT 0);
CREATE TABLE IF NOT EXISTS notes(id INTEGER PRIMARY KEY, msg TEXT, created TEXT);
CREATE TABLE IF NOT EXISTS push_subs(endpoint TEXT PRIMARY KEY, member_id INTEGER);");

const IMG = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
const VID = ['video/mp4'=>'mp4'];
const MAX_MB = 60; // tamaño máximo por archivo

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function setting($k){ global $db; $s=$db->prepare("SELECT v FROM settings WHERE k=?"); $s->execute([$k]); return (string)$s->fetchColumn(); }

// Sube un archivo validando su tipo real; devuelve el nombre guardado o null
function upload($field, $allow){ return upload_file($_FILES[$field] ?? null, $allow); }
function upload_file($f, $allow){
  if (!$f || $f['error'] !== UPLOAD_ERR_OK || $f['size'] > MAX_MB*1024*1024) return null;
  $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
  if (!isset($allow[$mime])) return null;
  if (!is_dir(BASE.'/uploads')) mkdir(BASE.'/uploads', 0755, true);
  $name = bin2hex(random_bytes(8)).'.'.$allow[$mime];
  return move_uploaded_file($f['tmp_name'], BASE.'/uploads/'.$name) ? $name : null;
}
// Borra un registro y su archivo (tabla y columna son fijas en el código)
function del($t, $id, $col){
  global $db;
  $s=$db->prepare("SELECT $col FROM $t WHERE id=?"); $s->execute([$id]);
  if ($f=$s->fetchColumn()) @unlink(BASE.'/uploads/'.basename($f));
  $db->prepare("DELETE FROM $t WHERE id=?")->execute([$id]);
}

// ===== Notificaciones push (Web Push con VAPID, sin librerías) =====
function save_setting($k,$v){ global $db; $db->prepare("INSERT OR REPLACE INTO settings(k,v) VALUES(?,?)")->execute([$k,$v]); }
function b64u($s){ return rtrim(strtr(base64_encode($s),'+/','-_'),'='); }
function vapid_pub(){
  if (!setting('vapid_pub')) {
    foreach ([[],['config'=>'C:/xampp/apache/conf/openssl.cnf'],['config'=>'C:/xampp/php/extras/openssl/openssl.cnf']] as $cfg) {
      $k = @openssl_pkey_new($cfg + ['curve_name'=>'prime256v1','private_key_type'=>OPENSSL_KEYTYPE_EC]);
      if ($k) {
        openssl_pkey_export($k, $pem, null, $cfg); $d = openssl_pkey_get_details($k)['ec'];
        save_setting('vapid_priv', $pem);
        save_setting('vapid_pub', b64u("\x04".str_pad($d['x'],32,"\0",STR_PAD_LEFT).str_pad($d['y'],32,"\0",STR_PAD_LEFT)));
        break;
      }
    }
  }
  return setting('vapid_pub');
}
function vapid_jwt($aud){
  $h = b64u(json_encode(['typ'=>'JWT','alg'=>'ES256']));
  $p = b64u(json_encode(['aud'=>$aud,'exp'=>time()+43200,'sub'=>'mailto:avisos@'.preg_replace('/:\d+$/','',$_SERVER['HTTP_HOST'] ?? 'example.com')]));
  openssl_sign("$h.$p", $der, setting('vapid_priv'), OPENSSL_ALGO_SHA256);
  $rl = ord($der[3]); $r = substr($der,4,$rl); $sl = ord($der[5+$rl]); $s = substr($der,6+$rl,$sl);
  $raw = str_pad(ltrim($r,"\0"),32,"\0",STR_PAD_LEFT).str_pad(ltrim($s,"\0"),32,"\0",STR_PAD_LEFT);
  return "$h.$p.".b64u($raw);
}
// Avisa a todos los dispositivos suscritos (el mensaje lo pide el celular a latest.php)
function push_all(){
  global $db; $pub = vapid_pub(); if (!$pub || !function_exists('curl_init')) return;
  foreach ($db->query("SELECT endpoint FROM push_subs")->fetchAll() as $s) {
    $u = parse_url($s['endpoint']); $aud = $u['scheme'].'://'.$u['host'].(isset($u['port']) ? ':'.$u['port'] : '');
    $ch = curl_init($s['endpoint']);
    curl_setopt_array($ch, [CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>'', CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>8,
      CURLOPT_HTTPHEADER=>['TTL: 86400','Content-Length: 0','Authorization: vapid t='.vapid_jwt($aud).', k='.$pub]]);
    curl_exec($ch);
    if (curl_errno($ch) == 60) { curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); curl_exec($ch); } // XAMPP sin certificados instalados
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($code == 404 || $code == 410) $db->prepare("DELETE FROM push_subs WHERE endpoint=?")->execute([$s['endpoint']]);
  }
}
function add_note($msg){ global $db; $db->prepare("INSERT INTO notes(msg,created) VALUES(?,?)")->execute([$msg, date('Y-m-d H:i')]); push_all(); }

// Carruseles ordenados: el fijado primero y los demás por orden de creación (los nuevos quedan debajo)
function albums(){
  global $db; $g = [];
  foreach ($db->query("SELECT * FROM gallery ORDER BY id ASC") as $r) $g[$r['grp']][] = $r;
  $first = []; foreach ($g as $k => $v) $first[$k] = min(array_column($v, 'id')); asort($first);
  $pin = setting('pinned_album'); $o = [];
  if ($pin !== '' && isset($g[$pin])) $o[$pin] = $g[$pin];
  foreach (array_keys($first) as $k) if (!isset($o[$k])) $o[$k] = $g[$k];
  return $o;
}