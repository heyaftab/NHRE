(() => {
  /* ---------- Authenticated sidebar ---------- */
  const sidebar = document.getElementById('nhreSidebar');
  if (sidebar) {
    const body = document.body;
    const toggle = document.querySelector('.sidebar-toggle');
    const collapse = document.querySelector('.sidebar-collapse');
    const backdrop = document.querySelector('.sidebar-backdrop');
    let sidebarCloseTimer;
    body.classList.add('has-sidebar');

    const setMobileOpen = (open) => {
      if (backdrop && open) {
        window.clearTimeout(sidebarCloseTimer);
        backdrop.hidden = false;
        requestAnimationFrame(() => body.classList.add('sidebar-open'));
      } else {
        body.classList.remove('sidebar-open');
        if (backdrop) sidebarCloseTimer = window.setTimeout(() => { backdrop.hidden = true; }, 260);
      }
      if (toggle) toggle.setAttribute('aria-expanded', String(open));
    };
    const closeMobile = () => setMobileOpen(false);

    if (toggle) toggle.addEventListener('click', () => setMobileOpen(!body.classList.contains('sidebar-open')));
    if (backdrop) backdrop.addEventListener('click', closeMobile);
    if (collapse) collapse.addEventListener('click', () => {
      body.classList.toggle('sidebar-collapsed');
      try { localStorage.setItem('nhre-sidebar-collapsed', body.classList.contains('sidebar-collapsed') ? '1' : '0'); } catch (e) {}
    });
    try { if (localStorage.getItem('nhre-sidebar-collapsed') === '1' && window.innerWidth > 991) body.classList.add('sidebar-collapsed'); } catch (e) {}
    sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMobile));
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeMobile(); });
  }

  'use strict';

  /* ---------- Light/dark theme ---------- */
  const THEME_KEY = 'nhre-theme';
  const root = document.documentElement;
  const preferredTheme = () => {
    try {
      const saved = localStorage.getItem(THEME_KEY);
      if (saved === 'light' || saved === 'dark') return saved;
    } catch (err) {}
    return window.matchMedia?.('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  };

  const existingThemeButton = document.getElementById('themeToggle');
  const themeButton = existingThemeButton || document.createElement('button');
  if (!existingThemeButton) {
    themeButton.type = 'button';
    themeButton.id = 'themeToggle';
  }

  const applyTheme = (theme, save = false) => {
    root.dataset.theme = theme;
    root.style.colorScheme = theme;
    const dark = theme === 'dark';
    themeButton.innerHTML = `<i class="fa-solid fa-${dark ? 'sun' : 'moon'}" aria-hidden="true"></i>`;
    themeButton.setAttribute('aria-label', `Switch to ${dark ? 'light' : 'dark'} theme`);
    themeButton.setAttribute('title', `${dark ? 'Light' : 'Dark'} theme`);
    themeButton.setAttribute('aria-pressed', String(dark));
    if (save) {
      try { localStorage.setItem(THEME_KEY, theme); } catch (err) {}
    }
  };

  applyTheme(preferredTheme());
  if (!existingThemeButton) {
    const navActions =
      document.querySelector('.dashboard-nav .container > div:last-child') ||
      document.querySelector('#navMain > .d-flex');
    if (navActions) {
      themeButton.className = 'theme-toggle theme-toggle-nav';
      navActions.appendChild(themeButton);
    } else {
      themeButton.className = 'theme-toggle';
      document.body.appendChild(themeButton);
    }
  }
  themeButton.addEventListener('click', () => {
    applyTheme(root.dataset.theme === 'dark' ? 'light' : 'dark', true);
  });

  /* ---------- Ripple effect ---------- */
  document.querySelectorAll('.ripple').forEach((el) => {
    el.addEventListener('click', (ev) => {
      const rect = el.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const ink = document.createElement('span');
      ink.className = 'ripple-ink';
      ink.style.width = ink.style.height = size + 'px';
      ink.style.left = ev.clientX - rect.left - size / 2 + 'px';
      ink.style.top = ev.clientY - rect.top - size / 2 + 'px';
      el.appendChild(ink);
      setTimeout(() => ink.remove(), 650);
    });
  });

  /* ---------- Navbar scroll state ---------- */
  const nav = document.getElementById('mainNav');
  if (nav) {
    const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 24);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ---------- Close mobile nav on link click ---------- */
  const navCollapse = document.getElementById('navMain');
  if (navCollapse) {
    navCollapse.querySelectorAll('.nav-link').forEach((link) => {
      link.addEventListener('click', () => {
        if (navCollapse.classList.contains('show')) {
          const inst = bootstrap.Collapse.getOrCreateInstance(navCollapse);
          inst.hide();
        }
      });
    });
  }

  /* ---------- Reveal on scroll ---------- */
  const revealEls = document.querySelectorAll('.reveal');
  const reveal = (el) => el.classList.add('revealed');

  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            reveal(entry.target);
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12 }
    );
    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach(reveal);
  }

  /* ---------- Animated counters ---------- */
  const counters = document.querySelectorAll('[data-count]');

  const animateCounter = (el) => {
    const target = parseFloat(el.dataset.count);
    const decimals = parseInt(el.dataset.decimal || '0', 10);
    const suffix = el.dataset.suffix || '';
    const duration = 1800;
    const start = performance.now();

    const step = (now) => {
      const p = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - p, 3);
      el.textContent = (target * eased).toFixed(decimals) + suffix;
      if (p < 1) requestAnimationFrame(step);
    };

    requestAnimationFrame(step);
  };

  if ('IntersectionObserver' in window) {
    const cio = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            animateCounter(entry.target);
            cio.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.4 }
    );
    counters.forEach((el) => cio.observe(el));
  } else {
    counters.forEach(animateCounter);
  }

  /* ---------- Strong password meter ---------- */
  const STRONG_RE = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
  const passwordField = document.getElementById('password');
  const confirmField = document.getElementById('confirm_password');
  const meterBar = document.querySelector('.password-meter > span');

  const scorePassword = (val) => {
    let score = 0;
    if (val.length >= 8) score++;
    if (/[a-z]/.test(val) && /[A-Z]/.test(val)) score++;
    if (/\d/.test(val)) score++;
    if (/[\W_]/.test(val)) score++;
    return score;
  };

  if (passwordField && meterBar && passwordField.dataset.strongPassword !== undefined) {
    passwordField.addEventListener('input', () => {
      const score = scorePassword(passwordField.value);
      meterBar.className = 'level-' + score;
      meterBar.style.width = (score / 4) * 100 + '%';
    });
  }

  /* ---------- Bootstrap custom validation + confirm match ---------- */
  document.querySelectorAll('.needs-validation').forEach((form) => {
    const clearValidity = (input) =>
      input.addEventListener('input', () => input.setCustomValidity(''));

    form.querySelectorAll('input, select').forEach(clearValidity);

    form.addEventListener(
      'submit',
      (ev) => {
        let valid = form.checkValidity();

        if (passwordField && passwordField.dataset.strongPassword !== undefined && passwordField.value !== '') {
          if (!STRONG_RE.test(passwordField.value)) {
            passwordField.setCustomValidity(
              'Use 8+ characters with uppercase, lowercase, number, and symbol.'
            );
            valid = false;
          }
        }

        if (confirmField && passwordField && confirmField.value !== passwordField.value) {
          confirmField.setCustomValidity('Passwords must match.');
          valid = false;
        }

        if (!valid) {
          ev.preventDefault();
          ev.stopPropagation();
          form.querySelector(':invalid')?.focus();
        }

        form.classList.add('was-validated');
      },
      false
    );
  });

  /* ---------- Notification bell + overlay ---------- */
  const bell = document.getElementById('notificationBell');
  if (bell) {
    const wrap = document.getElementById('notificationWrap');
    const badge = document.getElementById('notificationBadge');
    const overlay = document.getElementById('notificationOverlay');
    const list = document.getElementById('notificationList');
    const markAll = document.getElementById('markAllRead');
    const csrfInput = document.getElementById('notificationCsrf');
    const API_URL = 'auth/notifications_api.php';

    const escapeHtml = (value) =>
      String(value ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
      }[c]));

    const timeAgo = (datetime) => {
      const date = new Date(String(datetime).replace(' ', 'T'));
      if (isNaN(date.getTime())) return datetime;
      const diff = Math.max(0, (Date.now() - date.getTime()) / 1000);
      if (diff < 60) return 'just now';
      if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
      if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
      return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    };

    const updateBadge = (count) => {
      if (!badge) return;
      badge.textContent = count > 99 ? '99+' : String(count);
      badge.hidden = count === 0;
    };

    const renderList = (notifications) => {
      if (!list) return;
      if (!notifications.length) {
        list.innerHTML = '<div class="notification-empty">You\u2019re all caught up.</div>';
        return;
      }
      list.innerHTML = notifications
        .map(
          (n) => `
            <div class="notification-item${n.is_read ? '' : ' notification-item-unread'}">
              <div class="notification-item-title">${escapeHtml(n.title)}</div>
              <div class="notification-item-msg">${escapeHtml(n.message)}</div>
              <div class="notification-item-time">${escapeHtml(n.notification_type)} \u2022 ${timeAgo(n.created_at)}</div>
            </div>`
        )
        .join('');
    };

    const loadNotifications = async () => {
      try {
        const res = await fetch(API_URL, {
          headers: { Accept: 'application/json' },
          credentials: 'same-origin',
        });
        if (!res.ok) return;
        const data = await res.json();
        if (!data.ok) return;
        updateBadge(data.unread);
        renderList(data.notifications);
      } catch (err) {}
    };

    const markAllRead = async () => {
      if (!csrfInput) return;
      try {
        const res = await fetch(API_URL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
          },
          credentials: 'same-origin',
          body: '_csrf=' + encodeURIComponent(csrfInput.value),
        });
        if (res.ok) updateBadge(0);
        loadNotifications();
      } catch (err) {}
    };

    bell.addEventListener('click', () => {
      const willOpen = overlay.hidden;
      overlay.hidden = !willOpen;
      bell.setAttribute('aria-expanded', String(willOpen));
      if (willOpen) loadNotifications();
    });

  if (markAll) {
      markAll.addEventListener('click', (ev) => {
        ev.stopPropagation();
        markAllRead();
      });
    }

    document.addEventListener('click', (ev) => {
      if (!overlay.hidden && wrap && !wrap.contains(ev.target)) {
        overlay.hidden = true;
        bell.setAttribute('aria-expanded', 'false');
      }
    });

    document.addEventListener('keydown', (ev) => {
      if (ev.key === 'Escape' && !overlay.hidden) {
        overlay.hidden = true;
        bell.setAttribute('aria-expanded', 'false');
      }
    });

    loadNotifications();
    setInterval(loadNotifications, 30000);
  }

  /* ---------- Smooth page transitions ---------- */
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.querySelectorAll('a[href]').forEach((link) => {
      link.addEventListener('click', (event) => {
        const href = link.getAttribute('href');
        const isInternal = href && !href.startsWith('#') && !href.startsWith('http') && !href.startsWith('mailto:') && !href.startsWith('tel:');
        if (!isInternal || link.target === '_blank' || link.hasAttribute('download') || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        event.preventDefault();
        document.body.classList.add('page-leaving');
        window.setTimeout(() => { window.location.href = href; }, 180);
      });
    });
  }
})();
