// ---- blok oranye meluncur di tombol Masuk / Daftar ----
(function () {
  const box = document.getElementById('navAuth');
  if (!box) return;
  const pill  = box.querySelector('.pill');
  const links = [...box.querySelectorAll('.na')];
  let aktif = box.querySelector('.is-active') || links[links.length - 1];

  function pindah(el, animasi = true) {
    pill.classList.toggle('no-anim', !animasi);
    pill.style.left  = el.offsetLeft + 'px';
    pill.style.width = el.offsetWidth + 'px';
    links.forEach(l => l.classList.toggle('on', l === el));
  }

  links.forEach(l => {
    l.addEventListener('mouseenter', () => pindah(l));
    l.addEventListener('focus',      () => pindah(l));
    l.addEventListener('click',      () => { aktif = l; pindah(l); });
  });
  box.addEventListener('mouseleave', () => pindah(aktif));
  box.addEventListener('focusout',   () => pindah(aktif));

  pindah(aktif, false);
  window.addEventListener('resize', () => pindah(aktif, false));
  window.addEventListener('load',   () => pindah(aktif, false));
})();