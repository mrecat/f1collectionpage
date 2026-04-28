// ── Search debounce ──────────────────────────────
const searchInput = document.querySelector('input[name="q"]');
if (searchInput) {
  let timer;
  searchInput.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => searchInput.form.submit(), 550);
  });
}

// ── Animate stat bars ────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.bar-fill').forEach(el => {
    const target = el.style.width;
    el.style.width = '0';
    setTimeout(() => { el.style.width = target; }, 120);
  });
});

// ── Multi-image upload preview ───────────────────
function previewImages(input) {
  const preview = document.getElementById('uploadPreview');
  if (!preview) return;
  preview.innerHTML = '';
  preview.style.display = 'none';
  if (!input.files || !input.files.length) return;

  preview.style.display = 'flex';
  preview.style.flexWrap = 'wrap';
  preview.style.gap = '10px';
  preview.style.marginTop = '12px';
  preview.style.justifyContent = 'center';

  Array.from(input.files).slice(0, 5).forEach(file => {
    const reader = new FileReader();
    reader.onload = e => {
      const wrap = document.createElement('div');
      wrap.style.cssText = 'position:relative;width:100px;height:70px;border-radius:6px;overflow:hidden;border:1px solid var(--border2)';
      const img = document.createElement('img');
      img.src = e.target.result;
      img.style.cssText = 'width:100%;height:100%;object-fit:cover';
      wrap.appendChild(img);
      preview.appendChild(wrap);
    };
    reader.readAsDataURL(file);
  });
}

// Legacy single image preview (add.php)
function previewImage(input) {
  const preview = document.getElementById('uploadPreview');
  const img     = document.getElementById('previewImg');
  if (!preview || !img) return;
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => { img.src = e.target.result; preview.style.display = 'block'; };
    reader.readAsDataURL(input.files[0]);
  }
}

// ── Drag & drop ──────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const zone = document.getElementById('uploadZone');
  if (!zone) return;
  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
  zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('dragover');
    const fi = document.getElementById('fileInput');
    if (e.dataTransfer.files.length && fi) {
      fi.files = e.dataTransfer.files;
      if (fi.multiple) previewImages(fi);
      else             previewImage(fi);
    }
  });
});

// ── Carrusel últimas incorporaciones ─────────────
let _carIdx = 0;

function carouselGoTo(idx) {
  const track  = document.getElementById('carouselTrack');
  const dots   = document.querySelectorAll('.carousel-dot');
  const slides = document.querySelectorAll('.carousel-slide');
  if (!track || !slides.length) return;

  _carIdx = (idx + slides.length) % slides.length;
  track.style.transform = `translateX(-${_carIdx * 100}%)`;

  dots.forEach((d, i) => d.classList.toggle('active', i === _carIdx));

  // Ocultar/mostrar flechas si solo hay 1 slide
  const prev = document.querySelector('.carousel-prev');
  const next = document.querySelector('.carousel-next');
  if (prev && next) {
    const show = slides.length > 1;
    prev.style.visibility = show ? 'visible' : 'hidden';
    next.style.visibility = show ? 'visible' : 'hidden';
  }
}

function carouselMove(dir) { carouselGoTo(_carIdx + dir); }

// Touch / swipe support
document.addEventListener('DOMContentLoaded', () => {
  const track = document.getElementById('carouselTrack');
  if (!track) return;
  carouselGoTo(0); // init
  let startX = 0;
  track.addEventListener('touchstart', e => { startX = e.touches[0].clientX; }, { passive: true });
  track.addEventListener('touchend',   e => {
    const diff = startX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 40) carouselMove(diff > 0 ? 1 : -1);
  });
});
