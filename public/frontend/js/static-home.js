(() => {
  const toggle = document.querySelector('[data-menu-toggle]');
  const nav = document.getElementById('primary-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', String(!open));
      nav.classList.toggle('is-open', !open);
      document.body.classList.toggle('nav-open', !open);
    });
    nav.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
      toggle.setAttribute('aria-expanded', 'false');
      nav.classList.remove('is-open');
      document.body.classList.remove('nav-open');
    }));
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        toggle.setAttribute('aria-expanded', 'false');
        nav.classList.remove('is-open');
        document.body.classList.remove('nav-open');
      }
    });
  }

  const search = document.getElementById('market-search');
  const cards = Array.from(document.querySelectorAll('.market-card'));
  const empty = document.getElementById('no-markets');
  const filterMarkets = () => {
    const query = (search?.value || '').trim().toLocaleLowerCase();
    let visible = 0;
    cards.forEach((card) => {
      const show = card.textContent.toLocaleLowerCase().includes(query);
      card.hidden = !show;
      if (show) visible++;
    });
    if (empty) empty.hidden = visible !== 0;
  };
  search?.addEventListener('input', filterMarkets);
  document.addEventListener('keydown', (event) => {
    const target = event.target;
    const typing = target instanceof HTMLElement && (target.isContentEditable || ['INPUT','TEXTAREA','SELECT'].includes(target.tagName));
    if (event.key === '/' && !typing && search) { event.preventDefault(); search.focus(); }
    if (event.key === 'Escape' && document.activeElement === search) search.blur();
  });

  const backTop = document.querySelector('[data-back-top]');
  const updateBackTop = () => backTop?.classList.toggle('is-visible', window.scrollY > 600);
  window.addEventListener('scroll', updateBackTop, { passive: true });
  updateBackTop();
  backTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' }));

  // Progressive enhancement only: content stays visible if IntersectionObserver is unavailable.
  if ('IntersectionObserver' in window && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.documentElement.classList.add('has-reveal');
    const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
      if (entry.isIntersecting) { entry.target.classList.add('is-revealed'); observer.unobserve(entry.target); }
    }), { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
    document.querySelectorAll('.section-heading, .result-board-card, .market-card, .archive-feature, .chart-market, .guide-list article, .faq-item').forEach((node) => observer.observe(node));
  }
})();