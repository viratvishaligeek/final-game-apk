(() => {
  const toggle = document.getElementById('menu-toggle');
  const nav = document.getElementById('primary-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const expanded = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', String(!expanded));
      nav.classList.toggle('is-open', !expanded);
    });
    nav.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
      toggle.setAttribute('aria-expanded', 'false');
      nav.classList.remove('is-open');
    }));
  }

  const search = document.getElementById('market-search');
  const cards = Array.from(document.querySelectorAll('.market-card'));
  const noResults = document.getElementById('no-markets');
  const filters = Array.from(document.querySelectorAll('[data-filter]'));
  let activeFilter = 'all';

  function filterMarkets() {
    const query = (search?.value || '').trim().toLowerCase();
    let visible = 0;
    cards.forEach((card) => {
      const matchesText = (card.dataset.market || '').includes(query);
      const matchesPeriod = activeFilter === 'all' || card.dataset.period === activeFilter;
      const show = matchesText && matchesPeriod;
      card.hidden = !show;
      if (show) visible += 1;
    });
    if (noResults) noResults.hidden = visible !== 0;
  }

  search?.addEventListener('input', filterMarkets);
  filters.forEach((button) => button.addEventListener('click', () => {
    activeFilter = button.dataset.filter || 'all';
    filters.forEach((item) => {
      const active = item === button;
      item.classList.toggle('is-active', active);
      item.setAttribute('aria-pressed', String(active));
    });
    filterMarkets();
  }));

  document.addEventListener('keydown', (event) => {
    const target = event.target;
    const isTyping = target instanceof HTMLElement && (target.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName));
    if (event.key === '/' && !isTyping && search) {
      event.preventDefault();
      search.focus();
    }
    if (event.key === 'Escape' && search === document.activeElement && search) search.blur();
  });
})();
