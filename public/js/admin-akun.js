(() => {
  const panel = document.querySelector('.account-table-panel');
  const table = panel?.querySelector('[data-account-table]');
  const body = table?.querySelector('tbody');
  if (!panel || !table || !body) return;

  const rows = [...body.querySelectorAll('[data-account-row]')];
  const originalOrder = new Map(rows.map((row, index) => [row, index]));
  const emptyRow = body.querySelector('[data-account-empty]');
  const noResultsRow = body.querySelector('[data-account-no-results]');
  const search = document.querySelector('#account-search');
  const roleFilter = panel.querySelector('[data-account-role-filter]');
  const onlineFilter = panel.querySelector('[data-account-online-filter]');
  const sortField = panel.querySelector('[data-account-sort]');
  const sortDirection = panel.querySelector('[data-account-sort-direction]');
  const resultSummary = panel.querySelector('#account-results-summary');

  const getSortValue = (row, field) => {
    if (field === 'last-online' || field === 'created') {
      const value = row.dataset[field];
      return value ? Number(value) : null;
    }

    return (row.dataset[field] || '').trim().toLocaleLowerCase('id');
  };

  const apply = () => {
    const query = search?.value.trim().toLocaleLowerCase('id') || '';
    const role = roleFilter?.value || '';
    const online = onlineFilter?.value || '';
    const field = sortField?.value || '';
    const direction = sortDirection?.dataset.accountSortDirection === 'desc' ? -1 : 1;

    if (field) {
      rows.sort((a, b) => {
        const aValue = getSortValue(a, field);
        const bValue = getSortValue(b, field);
        if (aValue === null || bValue === null) {
          if (aValue === bValue) return originalOrder.get(a) - originalOrder.get(b);
          return aValue === null ? 1 : -1;
        }

        const result = typeof aValue === 'number'
          ? aValue - bValue
          : aValue.localeCompare(bValue, 'id', { numeric: true, sensitivity: 'base' });

        return result * direction || originalOrder.get(a) - originalOrder.get(b);
      });
    } else {
      rows.sort((a, b) => originalOrder.get(a) - originalOrder.get(b));
    }

    let visibleCount = 0;
    rows.forEach(row => {
      const matchesSearch = !query || row.dataset.search.toLocaleLowerCase('id').includes(query);
      const matchesRole = !role || row.dataset.role === role;
      const hasOnline = Boolean(row.dataset.lastOnline);
      const matchesOnline = !online || (online === 'online' ? hasOnline : !hasOnline);
      row.hidden = !(matchesSearch && matchesRole && matchesOnline);
      if (!row.hidden) visibleCount++;
      body.appendChild(row);
    });

    if (emptyRow) emptyRow.hidden = rows.length > 0;
    if (noResultsRow) noResultsRow.hidden = rows.length === 0 || visibleCount > 0;
    if (resultSummary) {
      resultSummary.textContent = rows.length === 0
        ? 'Belum ada akun.'
        : `Menampilkan ${visibleCount.toLocaleString('id-ID')} dari ${rows.length.toLocaleString('id-ID')} akun`;
    }
  };

  panel.querySelectorAll('[data-account-field]').forEach(input => {
    input.addEventListener('change', () => {
      const field = input.dataset.accountField;
      table.querySelectorAll('[data-account-column]').forEach(cell => {
        if (cell.dataset.accountColumn === field) {
          cell.classList.toggle('is-column-hidden', !input.checked);
        }
      });
    });
  });

  search?.addEventListener('input', apply);
  roleFilter?.addEventListener('change', apply);
  onlineFilter?.addEventListener('change', apply);
  sortField?.addEventListener('change', apply);
  sortDirection?.addEventListener('click', () => {
    const nextDirection = sortDirection.dataset.accountSortDirection === 'asc' ? 'desc' : 'asc';
    sortDirection.dataset.accountSortDirection = nextDirection;
    sortDirection.textContent = nextDirection === 'asc' ? 'Menaik (A–Z)' : 'Menurun (Z–A)';
    sortDirection.setAttribute(
      'aria-label',
      nextDirection === 'asc' ? 'Urutan menaik' : 'Urutan menurun'
    );
    apply();
  });

  panel.querySelectorAll('.account-tool').forEach(tool => {
    tool.addEventListener('toggle', () => {
      if (!tool.open) return;
      panel.querySelectorAll('.account-tool').forEach(other => {
        if (other !== tool) other.open = false;
      });
    });
  });

  document.addEventListener('click', event => {
    if (!panel.contains(event.target)) {
      panel.querySelectorAll('.account-tool[open]').forEach(tool => {
        tool.open = false;
      });
    }
  });

  apply();
})();
