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
  // Modal car navigation buttons
  const prevBtn = document.getElementById('modalCarPrev');
  const nextBtn = document.getElementById('modalCarNext');
  if (prevBtn) prevBtn.addEventListener('click', function(e) { e.stopPropagation(); modalCarMove(-1); });
  if (nextBtn) nextBtn.addEventListener('click', function(e) { e.stopPropagation(); modalCarMove(1); });

  document.querySelectorAll('.bar-fill').forEach(el => {
    const target = el.style.width;
    el.style.width = '0';
    setTimeout(() => { el.style.width = target; }, 120);
  });
});

// ── Modal + Gallery ──────────────────────────────
let _galleryImgs  = [];
let _galleryIdx   = 0;
let _currentCarId = null;

function openModal(car) {
  const overlay   = document.getElementById('carModal');
  const imgEl     = document.getElementById('modalImg');
  const noImgEl   = document.getElementById('modalNoImg');
  const galleryEl = document.getElementById('modalGallery');
  const navEl     = document.getElementById('galleryNav');
  const counterEl = document.getElementById('galleryCounter');

  document.getElementById('modalYear').textContent   = car.year;
  document.getElementById('modalTitle').textContent  = car.model;
  document.getElementById('modalDriver').textContent = car.driver || '—';

  const noteEl = document.getElementById('modalNote');
  if (car.note) { noteEl.textContent = car.note; noteEl.style.display = 'block'; }
  else            { noteEl.style.display = 'none'; }

  document.getElementById('modalMeta').innerHTML = [
    car.team  ? `<span class="modal-tag">🏎️ ${car.team}</span>`  : '',
    car.maker ? `<span class="modal-tag">🏭 ${car.maker}</span>` : '',
  ].join('');

  const editBtn = car.admin
    ? `<a href="?page=edit&id=${car.id}" class="btn btn-ghost btn-sm">✏️ EDITAR</a>`
    : '';
  document.getElementById('modalFooter').innerHTML =
    editBtn + `<button class="btn btn-ghost btn-sm" onclick="closeModal()">✕ CERRAR</button>`;

  // Gallery setup
  _galleryImgs = (car.imgs && car.imgs.length) ? car.imgs : (car.img ? [car.img] : []);
  _galleryIdx  = 0;

  if (_galleryImgs.length > 0) {
    noImgEl.style.display   = 'none';
    galleryEl.style.display = 'block';
    updateGallerySlide();
    navEl.style.display = _galleryImgs.length > 1 ? 'flex' : 'none';
    if (counterEl) counterEl.textContent = `1 / ${_galleryImgs.length}`;
  } else {
    galleryEl.style.display = 'none';
    noImgEl.style.display   = 'block';
  }

  _currentCarId = car.id;
  updateModalNavArrows();
  overlay.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function updateModalNavArrows() {
  const list  = window._carList || [];
  const idx   = list.findIndex(c => c.id == _currentCarId);
  const prevBtn = document.getElementById('modalCarPrev');
  const nextBtn = document.getElementById('modalCarNext');
  if (!prevBtn || !nextBtn) return;
  prevBtn.style.visibility = idx > 0                ? 'visible' : 'hidden';
  nextBtn.style.visibility = idx < list.length - 1  ? 'visible' : 'hidden';
}

function modalCarMove(dir) {
  const list = window._carList || [];
  const idx  = list.findIndex(c => c.id == _currentCarId);
  const next = list[idx + dir];
  if (next) openModal(next);
}

function updateGallerySlide() {
  const imgEl     = document.getElementById('modalImg');
  const counterEl = document.getElementById('galleryCounter');
  imgEl.src = _galleryImgs[_galleryIdx];
  if (counterEl) counterEl.textContent = `${_galleryIdx + 1} / ${_galleryImgs.length}`;
}

function galleryPrev() {
  _galleryIdx = (_galleryIdx - 1 + _galleryImgs.length) % _galleryImgs.length;
  updateGallerySlide();
}

function galleryNext() {
  _galleryIdx = (_galleryIdx + 1) % _galleryImgs.length;
  updateGallerySlide();
}

function closeModal() {
  document.getElementById('carModal').classList.remove('open');
  document.body.style.overflow = '';
}

function closeModalOnBg(e) {
  if (e.target === document.getElementById('carModal')) closeModal();
}

document.addEventListener('keydown', e => {
  if (!document.getElementById('carModal').classList.contains('open')) return;
  if (e.key === 'Escape') { closeModal(); return; }
  if (e.shiftKey) {
    if (e.key === 'ArrowRight') modalCarMove(1);
    if (e.key === 'ArrowLeft')  modalCarMove(-1);
  } else {
    if (e.key === 'ArrowRight') galleryNext();
    if (e.key === 'ArrowLeft')  galleryPrev();
  }
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
      // call whichever preview function exists
      if (fi.multiple) previewImages(fi);
      else             previewImage(fi);
    }
  });
});
