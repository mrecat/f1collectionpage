// ═══════════════════════════════════════════════════════════
// F1 COLLECTION — ENHANCEMENTS.JS
// Se suma DESPUÉS de app.js. No modifica ninguna función
// existente, solo agrega comportamiento nuevo sobre clases
// que ya existen en el HTML actual.
//
// Integración en index.php (una línea, después de app.js):
// <script src="enhancements.js"></script>
// ═══════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {

  // ── 1. Scroll reveal ──────────────────────────────────────
  // Aplica a las cards de colección y miniaturas (y, si existe,
  // a la card de detalle de auto). Si el navegador no soporta
  // IntersectionObserver, todo se muestra normal sin animación.
  const revealTargets = document.querySelectorAll('.coll-card, .mini-card');
  if (revealTargets.length && 'IntersectionObserver' in window) {
    revealTargets.forEach(el => el.classList.add('reveal'));

    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('reveal-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    revealTargets.forEach(el => io.observe(el));
  }

  // ── 2. Fade-in de imágenes al cargar ─────────────────────
  // Cubre las fotos de las cards y la miniatura de la tabla.
  const imgs = document.querySelectorAll(
    '.coll-card-img img, .mini-card-img img, .car-thumb'
  );
  imgs.forEach(img => {
    if (img.complete && img.naturalWidth > 0) {
      // Ya estaba en caché del navegador — no hace falta esperar el evento load
      img.classList.add('img-loaded');
    } else {
      img.addEventListener('load', () => img.classList.add('img-loaded'));
      img.addEventListener('error', () => img.classList.add('img-loaded')); // no bloquear el shimmer si falla
    }
  });

  // ── 3. Favorito — micro-pop al togglear ──────────────────
  // Los botones .fav-btn están dentro de un <form> que hace
  // submit real (favorites.php recarga la página), así que
  // el pop se ve en el instante del click, antes del reload.
  document.querySelectorAll('.fav-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      btn.classList.add('fav-pop');
      // No hace falta sacarla: la página recarga por el submit del form.
    });
  });

  // ── 4. Botones con estado "procesando" ───────────────────
  // Aplica a cualquier form POST (eliminar auto, eliminar imagen,
  // subir fotos, marcar favorito). Los forms GET (filtros) quedan
  // afuera automáticamente por el selector.
  document.querySelectorAll('form[method="post"]').forEach(form => {
    form.addEventListener('submit', (e) => {
      const btn = form.querySelector('button[type="submit"]');
      if (!btn) return;
      // Esperamos un tick: si el form tenía onsubmit="return confirm(...)"
      // (ej: eliminar auto) y el usuario cancela, e.defaultPrevented queda
      // en true y NO ponemos el spinner, porque no hay submit real.
      setTimeout(() => {
        if (!e.defaultPrevented) btn.classList.add('btn-loading');
      }, 0);
    });
  });

});
