const $  = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];

$('#burger')?.addEventListener('click', e => { e.stopPropagation(); $('#sb').classList.toggle('open'); });
document.addEventListener('click', e => {
  const sb = $('#sb');
  if (sb && !sb.contains(e.target)) sb.classList.remove('open');
});

$('#cari')?.addEventListener('input', e => {
  const q = e.target.value.toLowerCase().trim();
  $$('#tbody tr').forEach(r => { r.hidden = q !== '' && !r.textContent.toLowerCase().includes(q); });
});

$$('[data-count]').forEach(el => {
  const n = +el.dataset.count, mulai = performance.now(), durasi = 900;
  (function f(now) {
    const p = Math.min((now - mulai) / durasi, 1);
    el.textContent = Math.round(n * (1 - Math.pow(1 - p, 3))).toLocaleString('id-ID');
    if (p < 1) requestAnimationFrame(f);
  })(mulai);
});

const bukaModal = modal => {
  modal.classList.add('open');
  document.body.classList.add('lock');
  modal.querySelector('input:not([type=hidden]):not([type=password]), select')?.focus();
};

const tutupModal = modal => {
  modal.classList.remove('open');
  if (!$('.modal.open')) document.body.classList.remove('lock');
};

document.addEventListener('click', e => {
  const pemicu = e.target.closest('[data-modal]');
  if (pemicu) {
    const modal = $('#' + pemicu.dataset.modal);
    if (pemicu.dataset.status) modal.dataset.status = pemicu.dataset.status;
    const form = $('form', modal);
    form?.reset();
    if (pemicu.dataset.action && form) form.action = pemicu.dataset.action;
    $$('[data-isi]', modal).forEach(el => {
      const nilai = pemicu.dataset[el.dataset.isi];
      if (nilai === undefined) return;
      if (el.matches('input, select')) el.value = nilai;
      else el.textContent = nilai;
    });
    bukaModal(modal);
    return;
  }

  const modal = e.target.closest('.modal');
  if (modal && (e.target === modal || e.target.closest('[data-tutup]'))) tutupModal(modal);
});

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') $$('.modal.open').forEach(tutupModal);
});

if ($('.modal.open')) document.body.classList.add('lock');

const toast = $('#toast');
if (toast) {
  setTimeout(() => {
    toast.classList.add('pergi');
    setTimeout(() => toast.remove(), 350);
  }, 3500);
}

const grafik = $('#grafik');
if (grafik) {
  const area = $('#grafik-area');
  const ket = $('#grafik-ket');
  const tombol = $$('[data-periode]', grafik);

  const gambar = d => {
    const maks = Math.max(1, ...d.data.map(t => t.jumlah));
    area.style.setProperty('--kolom', d.data.length);
    area.innerHTML = d.data.map((t, i) => {
      const tinggi = t.jumlah ? Math.max(4, (t.jumlah / maks) * 100) : 0;
      return `<div class="day" title="${t.judul}: ${t.jumlah} booking">
        <span class="lbl">${t.label}</span>
        <div class="bars" style="--n:${i}"><i style="height:${tinggi}%"></i></div>
        <b class="nilai">${t.jumlah}</b>
      </div>`;
    }).join('');
    ket.textContent = `${d.keterangan} · ${d.total} booking`;
  };

  gambar(JSON.parse(grafik.dataset.awal));

  tombol.forEach(btn => btn.addEventListener('click', async () => {
    if (btn.classList.contains('on')) return;
    const sebelumnya = tombol.find(t => t.classList.contains('on'));
    tombol.forEach(t => t.classList.toggle('on', t === btn));
    area.classList.add('muat');
    try {
      const res = await fetch(`${grafik.dataset.url}?periode=${btn.dataset.periode}`, { headers: { Accept: 'application/json' } });
      if (!res.ok) throw new Error(res.status);
      gambar(await res.json());
    } catch {
      tombol.forEach(t => t.classList.toggle('on', t === sebelumnya));
      ket.textContent = 'Gagal memuat grafik, coba lagi.';
    }
    area.classList.remove('muat');
  }));
}
