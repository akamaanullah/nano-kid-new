/* Dealer apply + trade login (demo: any email + 4+ char password) */
(() => {
  const page = document.body.dataset.page;
  const { $, toast, authSet, authGet } = NK;

  if (page === 'login') {
    const form = $('#loginForm');
    form.addEventListener('submit', e => {
      e.preventDefault();
      const email = $('#loginEmail').value.trim();
      const pass = $('#loginPass').value;
      if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { toast('Enter a valid work email.', 'warn'); return; }
      if (pass.length < 4) { toast('Password must be at least 4 characters.', 'warn'); return; }
      const existing = authGet() || {};
      authSet({
        email,
        business: existing.business || email.split('@')[0],
        name: existing.name || 'Trade buyer',
        loggedInAt: new Date().toISOString()
      });
      const next = new URLSearchParams(location.search).get('next') || 'dashboard.html';
      location.href = next;
    });
  }

  if (page === 'apply') {
    $('#applyForm').addEventListener('submit', e => {
      e.preventDefault();
      const f = e.target;
      if (!f.checkValidity()) { f.reportValidity(); return; }
      const data = Object.fromEntries(new FormData(f).entries());
      const apps = JSON.parse(localStorage.getItem('nkw_applications') || '[]');
      apps.unshift({ ...data, at: new Date().toISOString(), status: 'Received' });
      localStorage.setItem('nkw_applications', JSON.stringify(apps));
      authSet({
        email: data.email,
        business: data.business,
        name: data.contact,
        city: data.city,
        type: data.type,
        volume: data.volume,
        loggedInAt: new Date().toISOString()
      });
      f.hidden = true;
      $('#applySuccess').hidden = false;
    });
  }
})();
