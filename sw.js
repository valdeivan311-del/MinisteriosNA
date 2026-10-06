self.addEventListener('install', e => self.skipWaiting());
self.addEventListener('activate', e => e.waitUntil(clients.claim()));
// Al llegar un aviso, el celular pide el texto más reciente y lo muestra
self.addEventListener('push', e => {
  e.waitUntil(
    fetch(new URL('latest.php', self.registration.scope), {cache:'no-store'}).then(r => r.json()).catch(() => ({msg:'Hay novedades en Nuevo Amanecer'}))
    .then(d => self.registration.showNotification('Nuevo Amanecer', {body:d.msg, icon:'icon-192.png', badge:'icon-192.png', data:{url:'cuenta.php'}}))
  );
});
self.addEventListener('notificationclick', e => {
  e.notification.close();
  e.waitUntil(clients.openWindow(new URL(e.notification.data.url, self.registration.scope)));
});
