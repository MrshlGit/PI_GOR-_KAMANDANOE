const $  = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];

// sidebar di HP
$('#burger')?.addEventListener('click', e => { e.stopPropagation(); $('#sb').classList.toggle('open'); });
document.addEventListener('click', e => {
  const sb = $('#sb');
  if (sb && !sb.contains(e.target)) sb.classList.remove('open');
});

// cari di tabel pemesanan
$('#cari')?.addEventListener('input', e => {
  const q = e.target.value.toLowerCase().trim();
  $$('#tbody tr').forEach(r => { r.hidden = q !== '' && !r.textContent.toLowerCase().includes(q); });
});

// angka menghitung naik
$$('[data-count]').forEach(el => {
  const n = +el.dataset.count, mulai = performance.now(), durasi = 900;
  (function f(now) {
    const p = Math.min((now - mulai) / durasi, 1);
    el.textContent = Math.round(n * (1 - Math.pow(1 - p, 3))).toLocaleString('id-ID');
    if (p < 1) requestAnimationFrame(f);
  })(mulai);
});