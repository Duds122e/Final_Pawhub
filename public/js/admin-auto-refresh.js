/**
 * PAWHUB admin live refresh — HTTP polling only (no WebSocket).
 */
(function () {
  'use strict';

  const POLL_MS = 2000;
  const DASHBOARD_POLL_MS = 2000;

  const root = document.querySelector('[data-auto-refresh]');
  if (!root) {
    return;
  }

  const mode = root.getAttribute('data-auto-refresh');
  const refreshUrl = root.getAttribute('data-refresh-url');
  const tbody = root.querySelector('[data-refresh-target="tbody"]');
  const pendingEl = root.querySelector('[data-refresh-target="pending"]');
  const statEls = {
    pets: root.querySelector('[data-stat="pets"]'),
    pending: root.querySelector('[data-stat="pending"]'),
    appointments: root.querySelector('[data-stat="appointments"]'),
    services: root.querySelector('[data-stat="services"]'),
  };

  let lastVersion = root.getAttribute('data-refresh-version') || '';
  let initialized = Boolean(lastVersion);
  let timer = null;
  let inFlight = false;

  function isEditing() {
    const active = document.activeElement;
    if (!active || !root.contains(active)) {
      return false;
    }
    const tag = active.tagName;
    return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || active.isContentEditable;
  }

  function isVisible() {
    return document.visibilityState === 'visible';
  }

  function showPulse() {
    root.classList.add('live-refresh-pulse');
    window.setTimeout(() => root.classList.remove('live-refresh-pulse'), 1200);
  }

  async function fetchJson(url) {
    const res = await fetch(url, {
      method: 'GET',
      credentials: 'same-origin',
      headers: { Accept: 'application/json', 'X-Requested-With': 'PAWHUB-LiveRefresh' },
    });
    if (!res.ok) {
      throw new Error('HTTP ' + res.status);
    }
    return res.json();
  }

  function applyDashboardStats(data) {
    if (data.pets !== undefined && statEls.pets) {
      statEls.pets.textContent = String(data.pets);
    }
    if (data.pending !== undefined && statEls.pending) {
      statEls.pending.textContent = String(data.pending);
    }
    if (data.appointments !== undefined && statEls.appointments) {
      statEls.appointments.textContent = String(data.appointments);
    }
    if (data.services !== undefined && statEls.services) {
      statEls.services.textContent = String(data.services);
    }
  }

  function replaceTbody(html) {
    if (!tbody) {
      return;
    }
    tbody.innerHTML = html;
  }

  async function pollOnce() {
    if (!isVisible() || isEditing() || inFlight || !refreshUrl) {
      return;
    }

    inFlight = true;
    try {
      if (mode === 'dashboard') {
        const data = await fetchJson(refreshUrl);
        if (!data.version) {
          return;
        }
        if (!initialized) {
          lastVersion = data.version;
          initialized = true;
          return;
        }
        if (data.version !== lastVersion) {
          applyDashboardStats(data);
          lastVersion = data.version;
          showPulse();
        }
        return;
      }

      const data = await fetchJson(refreshUrl);
      if (!data.version) {
        return;
      }
      if (!initialized) {
        lastVersion = data.version;
        initialized = true;
        return;
      }
      if (data.version !== lastVersion) {
        if (data.html) {
          replaceTbody(data.html);
        }
        if (pendingEl && data.pending !== undefined) {
          pendingEl.textContent = String(data.pending);
        }
        lastVersion = data.version;
        showPulse();
      }
    } catch (_err) {
      /* silent — next poll retries */
    } finally {
      inFlight = false;
    }
  }

  function start() {
    stop();
    const interval = mode === 'dashboard' ? DASHBOARD_POLL_MS : POLL_MS;
    timer = window.setInterval(pollOnce, interval);
    pollOnce();
  }

  function stop() {
    if (timer) {
      window.clearInterval(timer);
      timer = null;
    }
  }

  document.addEventListener('visibilitychange', () => {
    if (isVisible()) {
      pollOnce();
    }
  });

  start();
})();
