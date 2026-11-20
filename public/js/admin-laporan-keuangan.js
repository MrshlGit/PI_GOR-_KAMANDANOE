const formatRupiah = value => `Rp ${Math.round(value).toLocaleString('id-ID')}`;
const escapeText = value => String(value).replace(/[&<>"']/g, character => ({
  '&': '&amp;',
  '<': '&lt;',
  '>': '&gt;',
  '"': '&quot;',
  "'": '&#039;'
}[character]));

const chartElement = document.querySelector('#finance-chart');
if (chartElement) {
  const chart = JSON.parse(chartElement.dataset.chart);

  if (!chart.periods.length || !chart.courts.length) {
    chartElement.innerHTML = '<p class="finance-chart-empty">Data lapangan belum tersedia.</p>';
  } else {
    const allValues = chart.periods.flatMap(period => period.values);
    const maximum = Math.max(0, ...allValues);
    const tickStep = Math.max(50000, Math.ceil(maximum / 200000) * 50000);
    const tickCount = Math.max(1, Math.ceil(maximum / tickStep));
    const ticks = Array.from({ length: tickCount + 1 }, (_, index) => index * tickStep);

    const legend = chart.courts.map(court => `
      <span class="finance-chart-legend-item">
        <i class="finance-chart-swatch" style="background:${court.warna}"></i>
        ${escapeText(court.nama)}
      </span>
    `).join('');

    const maximumValue = tickCount * tickStep;
    const axisTicks = [...ticks].reverse().map(tick => `
      <span>${formatRupiah(tick)}</span>
    `).join('');
    const gridLines = ticks.slice(1, -1).map(tick => `
      <i class="finance-chart-grid-line" style="bottom:${tick / maximumValue * 100}%"></i>
    `).join('');
    const periodGroups = chart.periods.map(period => `
      <div class="finance-chart-group" aria-label="${escapeText(period.label)}">
        <div class="finance-chart-bars">
          ${chart.courts.map((court, index) => {
            const amount = period.values[index] || 0;
            const height = Math.min(100, amount / maximumValue * 100);
            return `
              <i
                class="finance-column"
                style="height:${height}%;background:${court.warna}"
                title="${escapeText(court.nama)} · ${escapeText(period.label)}: ${formatRupiah(amount)}"
                aria-label="${escapeText(court.nama)} · ${escapeText(period.label)}: ${formatRupiah(amount)}"
              ></i>
            `;
          }).join('')}
        </div>
        <span class="finance-period-label">${escapeText(period.label)}</span>
      </div>
    `).join('');

    chartElement.innerHTML = `
      <div class="finance-chart-legend">${legend}</div>
      <div class="finance-chart-scroll">
        <div class="finance-chart-columns" style="--period-count:${chart.periods.length};min-width:${Math.max(620, chart.periods.length * 78)}px">
          <div class="finance-chart-y-axis" aria-label="Nilai booking dalam rupiah">
            ${axisTicks}
          </div>
          <div class="finance-chart-plot" style="--tick-count:${tickCount}">
            <div class="finance-chart-grid" aria-hidden="true">${gridLines}</div>
            ${periodGroups}
          </div>
        </div>
      </div>
    `;
  }
}

const donutElement = document.querySelector('#finance-donut');
if (donutElement) {
  const categories = JSON.parse(donutElement.dataset.chart);
  const donut = donutElement.querySelector('.finance-donut');
  const legend = donutElement.querySelector('.finance-donut-legend');
  const total = categories.reduce((sum, category) => sum + Number(category.jumlah), 0);

  if (total > 0) {
    let start = 0;
    const slices = categories.map(category => {
      const end = start + Number(category.jumlah) / total * 100;
      const slice = `${category.warna} ${start}% ${end}%`;
      start = end;
      return slice;
    });
    donut.style.background = `conic-gradient(from -90deg, ${slices.join(', ')})`;
  } else {
    donut.setAttribute('aria-label', 'Belum ada data penjualan terbayar');
  }

  donut.style.setProperty('--finance-total', `"${total > 0 ? '100%' : '0%'}"`);
  legend.innerHTML = categories.map(category => {
    const amount = Number(category.jumlah);
    const percentage = total > 0 ? Math.round(amount / total * 100) : 0;
    return `
      <li>
        <i class="finance-chart-swatch" style="background:${category.warna}"></i>
        <span>${escapeText(category.label)}<small>${formatRupiah(amount)} · ${percentage}%</small></span>
      </li>
    `;
  }).join('');
}
