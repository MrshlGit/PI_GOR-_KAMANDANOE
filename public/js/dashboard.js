document.documentElement.classList.add('js');

const $  = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];

// ---- sidebar di HP ----
const sidebar = $('#sidebar');
$('#burger')?.addEventListener('click', e => {
  e.stopPropagation();
  sidebar.classList.toggle('open');
});
document.addEventListener('click', e => {
  if (!sidebar.contains(e.target)) sidebar.classList.remove('open');
});

// ---- dropdown (notifikasi & user) ----
$$('.dropdown').forEach(dd => {
  $('.trigger', dd).addEventListener('click', e => {
    e.stopPropagation();
    const sudahBuka = dd.classList.contains('open');
    $$('.dropdown').forEach(x => x.classList.remove('open'));
    dd.classList.toggle('open', !sudahBuka);
  });
});
document.addEventListener('click', () => $$('.dropdown').forEach(x => x.classList.remove('open')));

// ---- cari lapangan ----
$('#cari')?.addEventListener('input', e => {
  const q = e.target.value.toLowerCase().trim();
  $$('[data-nama]').forEach(k => {
    k.hidden = q !== '' && !k.dataset.nama.toLowerCase().includes(q);
  });
});

// ---- carousel otomatis ----
const track = $('#track');
if (track) {
  const slides = $$('.slide', track);
  const dots = $('#dots');
  const tampil = Math.max(1, Math.round(track.clientWidth / slides[0].offsetWidth));
  const halaman = Math.max(1, slides.length - tampil + 1);
  let idx = 0, timer;

  for (let i = 0; i < halaman; i++) {
    const b = document.createElement('button');
    b.setAttribute('aria-label', 'Slide ' + (i + 1));
    b.addEventListener('click', () => { go(i); play(); });
    dots.appendChild(b);
  }

  const tandai = () => $$('button', dots).forEach((b, i) => b.classList.toggle('on', i === idx));
  const go = i => {
    idx = (i + halaman) % halaman;
    track.scrollTo({ left: slides[idx].offsetLeft - track.offsetLeft, behavior: 'smooth' });
    tandai();
  };
  const play = () => { clearInterval(timer); timer = setInterval(() => go(idx + 1), 4000); };

  track.addEventListener('mouseenter', () => clearInterval(timer));
  track.addEventListener('mouseleave', play);
  track.addEventListener('scroll', () => {
    const i = Math.round(track.scrollLeft / (slides[0].offsetWidth + 16));
    if (i !== idx && i < halaman) { idx = i; tandai(); }
  }, { passive: true });

  tandai();
  play();
}

// ---- muncul pelan saat di-scroll ----
const io = new IntersectionObserver(es => es.forEach(e => {
  if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
}), { threshold: 0.12 });
$$('.fade-up').forEach(el => io.observe(el));