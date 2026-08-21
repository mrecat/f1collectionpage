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

// ═══════════════════════════════════════════════════════════
// RONDA 2 — chiches nuevos
// ═══════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {

  // ── 9. Trading card — hover 3D + brillo diagonal ─────────
  const tcCards = document.querySelectorAll('.coll-card, .mini-card');
  if (tcCards.length && matchMedia('(hover: hover)').matches) {
    tcCards.forEach(card => {
      const shine = document.createElement('div');
      shine.className = 'tc-shine';
      card.appendChild(shine);

      card.addEventListener('mousemove', (e) => {
        const r = card.getBoundingClientRect();
        const px = (e.clientX - r.left) / r.width;   // 0..1
        const py = (e.clientY - r.top) / r.height;    // 0..1
        const rotY = (px - 0.5) * 10;   // grados
        const rotX = (0.5 - py) * 10;
        card.style.transform = `perspective(700px) rotateX(${rotX}deg) rotateY(${rotY}deg) translateY(-4px)`;
        shine.style.backgroundPosition = `${px * 100}% ${py * 100}%`;
        card.classList.add('tc-active');
      });
      card.addEventListener('mouseleave', () => {
        card.style.transform = '';
        card.classList.remove('tc-active');
      });
    });
  }

  // ── 10. Stats — contador animado ─────────────────────────
  const kpiNums = document.querySelectorAll('.s2-kpi-n');
  if (kpiNums.length && 'IntersectionObserver' in window) {
    const animateCount = (el) => {
      const target = parseInt(el.textContent.replace(/\D/g, ''), 10);
      if (isNaN(target)) return;
      el.classList.add('counting');
      const duration = 900;
      const start = performance.now();
      function tick(now) {
        const p = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - p, 3); // ease-out cubic
        el.textContent = Math.round(target * eased);
        if (p < 1) requestAnimationFrame(tick);
        else el.textContent = target;
      }
      requestAnimationFrame(tick);
    };
    const kpiObs = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCount(entry.target);
          kpiObs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.4 });
    kpiNums.forEach(el => kpiObs.observe(el));
  }

  // ── 12. Zoom en la foto principal de la galería (car.php) ─
  const galMain = document.getElementById('galleryMain');
  if (galMain) {
    const lightbox = document.createElement('div');
    lightbox.className = 'gal-lightbox';
    lightbox.innerHTML = '<button class="gal-lightbox-close" aria-label="Cerrar">&times;</button><img>';
    document.body.appendChild(lightbox);
    const lbImg = lightbox.querySelector('img');

    function openLightbox() {
      const mainImg = document.getElementById('galleryMainImg');
      if (!mainImg) return;
      lbImg.src = mainImg.src;
      lightbox.classList.add('open');
    }
    function closeLightbox() { lightbox.classList.remove('open'); }

    galMain.addEventListener('click', (e) => {
      // No abrir si clickearon una flecha prev/next
      if (e.target.closest('.car-gal-arrow')) return;
      openLightbox();
    });
    lightbox.addEventListener('click', closeLightbox);
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && lightbox.classList.contains('open')) closeLightbox();
    });
  }

  // ── 13. Timeline — riel de progreso al hacer scroll ──────
  const erasGrid = document.getElementById('erasGrid');
  if (erasGrid) {
    const rail = document.createElement('div');
    rail.className = 'tl-progress-rail';
    rail.innerHTML = '<div class="tl-progress-fill" id="tlProgressFill"></div>';
    document.body.appendChild(rail);
    const fill = document.getElementById('tlProgressFill');

    function updateRail() {
      const r = erasGrid.getBoundingClientRect();
      const vh = window.innerHeight;
      const total = r.height + vh;
      const scrolled = Math.min(Math.max(vh - r.top, 0), total);
      fill.style.height = (scrolled / total * 100) + '%';
    }
    updateRail();
    window.addEventListener('scroll', updateRail, { passive: true });
    window.addEventListener('resize', updateRail);
  }

  // ── 14. Toasts — reemplazan los .alert estáticos ─────────
  const alerts = document.querySelectorAll('.alert');
  if (alerts.length) {
    let container = document.getElementById('f1-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'f1-toast-container';
      document.body.appendChild(container);
    }
    alerts.forEach((alert, i) => {
      const toast = document.createElement('div');
      toast.className = 'f1-toast' + (alert.classList.contains('alert-error') ? ' f1-toast-error' : '');
      toast.innerHTML = alert.innerHTML;
      container.appendChild(toast);
      alert.remove(); // saca el banner estático (el toast lo reemplaza)

      setTimeout(() => toast.classList.add('f1-toast-show'), 30 + i * 90);
      setTimeout(() => {
        toast.classList.remove('f1-toast-show');
        toast.classList.add('f1-toast-hide');
        setTimeout(() => toast.remove(), 350);
      }, 4200 + i * 90);
    });
  }

  // ── 16. Command palette (Ctrl+K) ─────────────────────────
  const cmdkData = window.__F1_SEARCH_DATA__ || [];
  const cmdkOverlay = document.getElementById('cmdkOverlay');
  if (cmdkOverlay && cmdkData.length) {
    const cmdkInput   = document.getElementById('cmdkInput');
    const cmdkResults = document.getElementById('cmdkResults');
    let activeIdx = -1;
    let currentItems = [];

    function norm(s) {
      return (s || '').toString().toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    }

    function renderResults(query) {
      cmdkResults.innerHTML = '';
      activeIdx = -1;
      if (!query.trim()) {
        cmdkResults.innerHTML = '<div class="cmdk-hint">Escribí un piloto, escudería, modelo o año…</div>';
        currentItems = [];
        return;
      }
      const q = norm(query);
      const matches = cmdkData.filter(c =>
        norm(c.t).includes(q) || norm(c.m).includes(q) ||
        norm(c.d).includes(q) || String(c.y).includes(q)
      ).slice(0, 8);

      currentItems = matches;
      if (!matches.length) {
        cmdkResults.innerHTML = '<div class="cmdk-empty">Sin resultados para "' + query + '"</div>';
        return;
      }
      matches.forEach((c, i) => {
        const item = document.createElement('div');
        item.className = 'cmdk-item';
        item.dataset.idx = i;
        item.innerHTML =
          '<span class="cmdk-item-year">' + c.y + '</span>' +
          '<span class="cmdk-item-main">' + c.t + ' ' + c.m + '</span>' +
          '<span class="cmdk-item-sub">' + (c.d || '') + '</span>';
        item.addEventListener('click', () => goTo(c));
        cmdkResults.appendChild(item);
      });
    }

    function goTo(car) {
      window.location.href = '?page=car&slug=' + encodeURIComponent(car.s);
    }

    function openCmdk() {
      cmdkOverlay.classList.add('open');
      cmdkInput.value = '';
      renderResults('');
      setTimeout(() => cmdkInput.focus(), 50);
    }
    function closeCmdk() {
      cmdkOverlay.classList.remove('open');
    }

    document.addEventListener('keydown', (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        cmdkOverlay.classList.contains('open') ? closeCmdk() : openCmdk();
      } else if (e.key === 'Escape' && cmdkOverlay.classList.contains('open')) {
        closeCmdk();
      }
    });
    cmdkOverlay.addEventListener('click', (e) => {
      if (e.target === cmdkOverlay) closeCmdk();
    });
    cmdkInput.addEventListener('input', () => renderResults(cmdkInput.value));
    cmdkInput.addEventListener('keydown', (e) => {
      const items = cmdkResults.querySelectorAll('.cmdk-item');
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIdx = Math.min(activeIdx + 1, items.length - 1);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIdx = Math.max(activeIdx - 1, 0);
      } else if (e.key === 'Enter') {
        if (currentItems[activeIdx]) goTo(currentItems[activeIdx]);
        else if (currentItems[0]) goTo(currentItems[0]);
        return;
      } else {
        return;
      }
      items.forEach((it, i) => it.classList.toggle('active', i === activeIdx));
      if (items[activeIdx]) items[activeIdx].scrollIntoView({ block: 'nearest' });
    });
  }

  // ── 17. Volver arriba — con anillo de progreso ───────────
  const backToTop = document.getElementById('backToTop');
  const backToTopRing = document.getElementById('backToTopRing');
  if (backToTop && backToTopRing) {
    const RING_LEN = 107; // 2 * PI * 17
    function updateBackToTop() {
      const scrollTop = window.scrollY;
      const docHeight = document.documentElement.scrollHeight - window.innerHeight;
      const pct = docHeight > 0 ? Math.min(scrollTop / docHeight, 1) : 0;
      backToTopRing.style.strokeDashoffset = RING_LEN - (RING_LEN * pct);
      backToTop.classList.toggle('visible', scrollTop > 400);
    }
    window.addEventListener('scroll', updateBackToTop, { passive: true });
    updateBackToTop();
    backToTop.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // ── 15. Barra de progreso de navegación ──────────────────
  // Se muestra al clickear un link interno que navega a otra
  // página PHP (no afecta anchors #, ni links externos, ni
  // los que abren en pestaña nueva).
  const navBar = document.createElement('div');
  navBar.id = 'f1-nav-progress';
  document.body.appendChild(navBar);

  document.addEventListener('click', (e) => {
    const link = e.target.closest('a[href]');
    if (!link) return;
    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
    if (link.target === '_blank' || e.ctrlKey || e.metaKey || e.shiftKey) return;
    // Solo mismo origen (navegación real dentro de la app)
    try {
      const url = new URL(href, window.location.href);
      if (url.origin !== window.location.origin) return;
    } catch (err) { return; }

    navBar.style.width = '0%';
    navBar.classList.remove('f1-nav-done');
    requestAnimationFrame(() => { navBar.style.width = '75%'; });
  });
  window.addEventListener('pageshow', () => {
    navBar.classList.add('f1-nav-done');
  });

});
