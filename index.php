<?php require __DIR__."/inc.php";
$mem=null; $unread=0;
if(!empty($_SESSION['m'])){ $s=$db->prepare("SELECT * FROM members WHERE id=?"); $s->execute([$_SESSION['m']]); $mem=$s->fetch();
  if($mem){ $s=$db->prepare("SELECT COUNT(*) FROM notes WHERE id>?"); $s->execute([$mem['last_note']]); $unread=(int)$s->fetchColumn(); } }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Ministerios Nuevo Amanecer</title>
<script>document.documentElement.classList.add('js')</script>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="apple.css">
<?php $ga=setting('ga_id'); if(preg_match('/^G-[A-Z0-9]+$/',$ga)): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?=e($ga)?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?=e($ga)?>');</script>
<?php endif; ?>
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#050505">
<link rel="apple-touch-icon" href="icon-192.png">
<script src="app.js" defer></script>
<script src="apple.js" defer></script>
</head>
<body>
<nav><div class="wrap">
  <a href="#inicio"><img class="lg" src="logo.jpg" alt="MNA"></a>
  <button class="burger" aria-label="Menú"><span></span><span></span></button>
  <div class="links"><a href="#servicios">Servicios</a><a href="#ministerios">Ministerios</a><a href="#eventos">Eventos</a><a href="historia.html">Historia</a><a href="#contacto">Contacto</a><a href="cuenta.php"><?= $mem ? "🔔".($unread ? " ".$unread : "") : "Entrar" ?></a></div>
</div></nav>

<header class="hero" id="inicio">
  <div class="bgw"><img src="logo.svg" alt=""></div>
  <div class="wrap">
    <span class="eyebrow fade">Ministerios</span>
    <h1><span class="l"><span>Nuevo</span></span><span class="l"><span class="grad">Amanecer</span></span></h1>
    <p class="q fade">“Que tu ministerio de excelencia sea para Dios y no para los hombres”</p>
    <div class="fade"><a class="btn" href="#servicios">Nuestros servicios</a><a class="btn o" href="#contacto">Visítanos</a></div>
  </div>
</header>

<div class="mq"><div>
  <span>Adoración</span><b>✦</b><span>Palabra</span><b>✦</b><span>Comunidad</span><b>✦</b><span>Fe</span><b>✦</b><span>Familia</span><b>✦</b>
  <span>Adoración</span><b>✦</b><span>Palabra</span><b>✦</b><span>Comunidad</span><b>✦</b><span>Fe</span><b>✦</b><span>Familia</span><b>✦</b>
</div></div>

<section id="servicios"><div class="wrap">
  <span class="eyebrow rv">Te esperamos</span>
  <h2 class="h2 grad rv">Servicios</h2>
  <div class="svc">
    <div class="card rv" style="--i:1"><h3>Domingo</h3><div class="h">10:00 AM</div><p>Servicio general de adoración y palabra para toda la familia.</p></div>
    <div class="card rv" style="--i:2"><h3>Miércoles</h3><div class="h">7:00 PM</div><p>Estudio bíblico y tiempo de oración.</p></div>
    <div class="card rv" style="--i:3"><h3>Viernes</h3><div class="h">7:00 PM</div><p>Servicio de oración.</p></div>
  </div>
</div></section>

<section class="salmo"><div class="wrap">
  <span class="eyebrow">Salmo</span>
  <span class="num">51:10</span>
  <p>Crea en mí, oh Dios, un corazón limpio y renueva un espíritu recto en mí</p>
</div></section>

<section id="ministerios"><div class="wrap">
  <span class="eyebrow rv">Salmos, himnos y canciones espirituales</span>
  <h2 class="h2 grad rv">Ministerios</h2>
  <div class="fr rv">
    <!-- TARJETAS: cambia la foto en url('img/...'), el nombre y el texto de abajo. El color de la etiqueta es --c -->
    <div class="fc" style="--n:0;--c:#f2e014"><i style="background-image:url('img/NewDawn.jpeg')"></i><em>Ministerio</em><b>Alabanza<br>New Dawn</b><small>Adoración</small></div>
    <div class="fc" style="--n:1;--c:#ff9d2e"><i style="background-image:url('img/jovenes.jpg')"></i><em>Ministerio</em><b>Jóvenes</b><small>Nueva generación</small></div>
    <div class="fc" style="--n:2;--c:#fff3c4"><i style="background-image:url('img/mujeres.jpg')"></i><em>Ministerio</em><b>Mujeres</b><small>Mujeres de fe</small></div>
    <div class="fc" style="--n:3;--c:#ffd447"><i style="background-image:url('img/mna-kids.jpg')"></i><em>Ministerio</em><b>Mna Kids</b><small>Los más pequeños</small></div>
    <div class="fc" style="--n:4;--c:#d9b24a"><i style="background-image:url('img/artes.jpg')"></i><em>Ministerio</em><b>Artes</b><small>Creatividad</small></div>
    <div class="fc" style="--n:5;--c:#ffffff"><i style="background-image:url('img/Varones.jpeg')"></i><em>Ministerio</em><b>Varones</b><small>Hombres de Dios</small></div>
  </div>
</div></section>

<?php $g=albums(); if($g): ?>
<section id="eventos"><div class="wrap">
  <span class="eyebrow rv">Momentos que vivimos juntos</span>
  <h2 class="h2 grad rv">FOTOGRAFIAS</h2>
  <?php foreach($g as $name=>$items): ?>
  <div class="car rv">
    <h3><?=e($name)?></h3>
    <button class="prev" aria-label="Anterior">‹</button><button class="next" aria-label="Siguiente">›</button>
    <div class="track">
      <?php foreach($items as $i): ?><div class="slide" style="background-image:url('uploads/<?=e($i['file'])?>')"><span><?=e($i['caption'])?></span></div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div></section>
<?php else: ?>
<section id="eventos"><div class="wrap">
  <span class="eyebrow rv">Momentos que vivimos juntos</span>
  <h2 class="h2 grad rv">FOTOGRAFIAS</h2>
  <div class="car rv"><h3></h3>
    <button class="prev" aria-label="Anterior">‹</button><button class="next" aria-label="Siguiente">›</button>
    <div class="track"><div class="slide"><span>Pronto compartiremos fotos</span></div><div class="slide"><span>Pronto compartiremos fotos</span></div><div class="slide"><span>Pronto compartiremos fotos</span></div></div>
  </div>
</div></section>
<?php endif; ?>

<?php $rl=$db->query("SELECT * FROM reels ORDER BY id DESC")->fetchAll(); if($rl): ?>
<section id="reels" style="padding-top:0"><div class="wrap">
  <span class="eyebrow rv">Míranos en Instagram</span>
  <h2 class="h2 grad rv">Reels</h2>
  <div class="car rv">
    <button class="prev" aria-label="Anterior">‹</button><button class="next" aria-label="Siguiente">›</button>
    <div class="track">
      <?php foreach($rl as $r): ?><div class="reel" <?= $r['file'] ? 'data-video="uploads/'.e($r['file']).'"' : 'data-reel="'.e($r['url']).'"' ?>></div>
      <?php endforeach; ?>
    </div>
  </div>
  <p style="text-align:center"><a class="btn o" href="https://instagram.com/">Ver más en Instagram</a></p>
</div></section>
<?php endif; ?>

<?php $ev=$db->prepare("SELECT * FROM events WHERE date>=? ORDER BY date LIMIT 1"); $ev->execute([date('Y-m-d')]); $ev=$ev->fetch();
if($ev): $fd=date('d/m/Y',strtotime($ev['date'])); ?>
<section id="proximo" style="padding-top:0"><div class="wrap ev">
  <div class="rv">
    <span class="eyebrow">Próximo evento</span>
    <h2><?=e($ev['title'])?></h2>
    <p><?=e($ev['descr'])?> · <?=e($fd)?></p>
    <a class="btn" href="https://wa.me/520000000000">Quiero ir</a>
  </div>
  <div class="evbox rv"<?php if($ev['img']): ?> style="background-image:linear-gradient(#000b,#000b),url('uploads/<?=e($ev['img'])?>')"<?php endif; ?>>
    <span class="eyebrow">Faltan</span>
    <div class="cd" data-d="<?=e($ev['date'])?>"><div><b>00</b><small>Días</small></div><div><b>00</b><small>Horas</small></div><div><b>00</b><small>Min</small></div><div><b>00</b><small>Seg</small></div></div>
  </div>
</div></section>
<?php else: ?>
<section id="proximo" style="padding-top:0"><div class="wrap ev">
  <div class="rv"><span class="eyebrow">Próximo evento</span><h2>Muy pronto</h2><p>Pronto anunciaremos nuestro próximo evento. Síguenos para enterarte.</p><a class="btn" href="https://wa.me/520000000000">Escríbenos</a></div>
</div></section>
<?php endif; ?>

<section class="teaser"><div class="wrap">
  <span class="eyebrow rv">Quiénes somos</span>
  <h2 class="h2 grad rv">Nuestra historia</h2>
  <p class="rv">Conoce cómo comenzó Ministerios Nuevo Amanecer y lo que Dios ha hecho a lo largo del camino.</p>
  <a class="btn rv" href="historia.html">Leer nuestra historia</a>
</div></section>

<section id="contacto" class="contacto"><div class="wrap">
  <span class="eyebrow rv">Encuéntranos</span>
  <h2 class="h2 grad rv">Contacto</h2>
  <p class="rv">Ote 9 287, Colonia Reforma, Ciudad Nezahualcóyotl</p>
  <p class="rv">WhatsApp: 00 0000 0000</p>
  <div class="rv" style="margin-top:24px">
    <a class="btn" href="https://wa.me/520000000000">WhatsApp</a>
    <a class="btn o" href="https://facebook.com/">Facebook</a>
    <a class="btn o" href="https://instagram.com/">Instagram</a>
    <a class="btn o" href="https://youtube.com/">YouTube</a>
  </div>
</div></section>

<footer><button class="btn o instalar" hidden>📲 Instalar app</button><br><br>Ministerios Nuevo Amanecer</footer>
</body>
</html>