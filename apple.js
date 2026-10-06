(function(){
var d=document,h=d.documentElement,nav=d.querySelector('nav');
// aparecer al hacer scroll
var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('in');io.unobserve(e.target)}})},{threshold:.12});
d.querySelectorAll('.rv').forEach(function(el,i){io.observe(el)});
// scroll: menú de vidrio, parallax y progreso del salmo
var salmo=d.querySelector('.salmo'),tick=false;
function onScroll(){
  var y=window.scrollY;nav.classList.toggle('sc',y>20);h.style.setProperty('--sy',y);
  if(salmo){var r=salmo.getBoundingClientRect(),p=1-(r.top+r.height*.4)/(innerHeight+r.height*.4);salmo.style.setProperty('--p',Math.max(0,Math.min(1,p)))}
  tick=false}
addEventListener('scroll',function(){if(!tick){tick=true;requestAnimationFrame(onScroll)}},{passive:true});onScroll();
// menú móvil
var b=d.querySelector('.burger');
if(b){b.onclick=function(){nav.classList.toggle('open')};d.querySelectorAll('.links a').forEach(function(a){a.onclick=function(){nav.classList.remove('open')}})}
// luz que sigue al cursor
d.querySelectorAll('.card').forEach(function(c){c.addEventListener('pointermove',function(e){var r=c.getBoundingClientRect();c.style.setProperty('--mx',(e.clientX-r.left)+'px');c.style.setProperty('--my',(e.clientY-r.top)+'px')})});
// reels
d.querySelectorAll('.reel').forEach(function(r){
  if(r.dataset.video){r.innerHTML='<video controls playsinline preload="metadata" src="'+r.dataset.video+'"></video>';return}
  var v=(r.dataset.reel||'').trim();if(!v)return;
  var m=v.match(/\/(?:reel|reels|p)\/([^\/?#]+)/),code=m?m[1]:v;
  r.innerHTML='<iframe src="https://www.instagram.com/reel/'+code+'/embed" loading="lazy" allowfullscreen></iframe>'});
// carruseles
d.querySelectorAll('.car').forEach(function(c){var t=c.querySelector('.track');
  c.querySelector('.prev').onclick=function(){t.scrollBy({left:-t.clientWidth*.8,behavior:'smooth'})};
  c.querySelector('.next').onclick=function(){t.scrollBy({left:t.clientWidth*.8,behavior:'smooth'})}});
// cuenta regresiva
var cd=d.querySelector('.cd');
if(cd){var t0=new Date(cd.dataset.d+'T00:00:00').getTime(),n=cd.querySelectorAll('b');
  function u(){var s=Math.max(0,Math.floor((t0-Date.now())/1000));
    [Math.floor(s/86400),Math.floor(s%86400/3600),Math.floor(s%3600/60),s%60].forEach(function(v,i){n[i].textContent=String(v).padStart(2,'0')})}
  u();setInterval(u,1000)}
// tarjetas de ministerios: inclinación 3D con el mouse
d.querySelectorAll('.fc').forEach(function(c){
  c.addEventListener('pointermove',function(e){if(e.pointerType!=='mouse')return;var r=c.getBoundingClientRect(),x=(e.clientX-r.left)/r.width-.5,y=(e.clientY-r.top)/r.height-.5;c.style.transform='rotateY('+(x*16)+'deg) rotateX('+(-y*16)+'deg) scale(1.04)'});
  c.addEventListener('pointerleave',function(){c.style.transform=''})});
})();