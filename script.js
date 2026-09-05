/* ============================================================
   MindfulSpace — script.js (Multi-Page Version)
   Each feature only runs if its elements exist on the page.
   ============================================================ */

const API = {
  auth:      'auth.php',
  mood:      'mood.php',
  resources: 'resources.php',
  wall:      'wall.php',
  contact:   'contact.php',
};

// Helper: only run if element exists on this page
function el(id) { return document.getElementById(id); }
function els(sel) { return document.querySelectorAll(sel); }

async function apiGet(url) {
  try { const r = await fetch(url); return await r.json(); }
  catch { return { error: 'Network error' }; }
}

async function apiPost(url, data) {
  try {
    const r = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });
    return await r.json();
  } catch { return { error: 'Network error' }; }
}

let currentUser = null;

/* ============================================================
   AFFIRMATIONS — only runs on index.html
   ============================================================ */
const AFFIRMATIONS = [
  "You are enough, exactly as you are right now.",
  "Your feelings are valid, and it's okay to feel them.",
  "Every small step forward is still progress.",
  "You have survived every difficult day so far. That's 100%.",
  "Rest is productive. Taking care of yourself matters.",
  "You are worthy of love, kindness, and compassion.",
  "Healing is not linear — and that's perfectly okay.",
  "Your presence in this world makes a difference.",
  "It's okay to ask for help. Strength lives in vulnerability.",
  "You don't have to have it all figured out today.",
  "You are braver than you believe and stronger than you know.",
  "This moment is hard, but it will pass. You are resilient.",
  "Growth happens slowly, then all at once. Keep going.",
  "Your mental health deserves the same care as your physical health.",
  "You are not your worst day. You are so much more.",
  "Being kind to yourself is a radical act of courage.",
  "Whatever you're carrying, you don't have to carry it alone.",
];

let lastAffirmationIndex = -1;

function getRandomAffirmation() {
  let idx;
  do { idx = Math.floor(Math.random() * AFFIRMATIONS.length); }
  while (idx === lastAffirmationIndex);
  lastAffirmationIndex = idx;
  return AFFIRMATIONS[idx];
}

function initAffirmation() {
  const textEl = el('affirmation-text');
  const btn    = el('new-affirmation-btn');
  if (!textEl) return; // not on this page

  textEl.textContent = getRandomAffirmation();

  if (btn) {
    btn.addEventListener('click', () => {
      textEl.classList.add('fade-out');
      setTimeout(() => {
        textEl.textContent = getRandomAffirmation();
        textEl.classList.remove('fade-out');
        textEl.classList.add('fade-in');
        setTimeout(() => textEl.classList.remove('fade-in'), 400);
      }, 400);
    });
  }
}

/* ============================================================
   MOOD CHECK-IN — only runs on checkin.html
   ============================================================ */
const MOOD_MESSAGES = {
  great:   "🌟 That's wonderful! Carry this energy with you — and don't forget to celebrate it.",
  good:    "😊 Good is great! You're doing well. Keep nurturing what's working.",
  okay:    "🌤️ Okay is a valid place to be. Take it easy on yourself today.",
  stressed:"💆 Stress is hard. Try the breathing exercise — you've got this.",
  sad:     "💙 It's okay to feel sad. You don't have to rush through it. Be gentle with yourself.",
  anxious: "🤍 Anxiety is tough, but you're here and that takes courage. One breath at a time.",
};

function getMoodHistoryLocal() {
  try { return JSON.parse(localStorage.getItem('ms_mood_history')) || []; }
  catch { return []; }
}

async function saveMoodEntry(mood, label, emoji) {
  if (currentUser) {
    await apiPost(`${API.mood}?action=save`, { mood, label, emoji });
  } else {
    const history = getMoodHistoryLocal();
    history.unshift({ mood, label, emoji, ts: Date.now() });
    localStorage.setItem('ms_mood_history', JSON.stringify(history.slice(0, 30)));
  }
}

function applyMoodTheme(mood) {
  document.body.className = document.body.className.replace(/mood-\w+/g, '').trim();
  document.body.classList.add(`mood-${mood}`);
}

function showMoodMessage(mood) {
  const msgEl = el('mood-message');
  if (!msgEl) return;
  msgEl.textContent = MOOD_MESSAGES[mood] || '';
  msgEl.classList.add('show');
  setTimeout(() => { msgEl.style.opacity = '0'; }, 4500);
  setTimeout(() => { msgEl.classList.remove('show'); msgEl.style.opacity = ''; }, 5000);
}

function formatDate(ts) {
  return new Date(ts).toLocaleDateString('en-PK', { month: 'short', day: 'numeric' });
}
function formatTime(ts) {
  return new Date(ts).toLocaleTimeString('en-PK', { hour: '2-digit', minute: '2-digit' });
}

async function renderHistory() {
  const container = el('history-list');
  if (!container) return; // not on this page

  if (currentUser) {
    const data = await apiGet(`${API.mood}?action=history`);
    const history = data.history || [];
    if (!history.length) {
      container.innerHTML = '<p class="empty-msg">No check‑ins yet — how are you feeling today? 🌿</p>';
      return;
    }
    container.innerHTML = history.map(e => `
      <div class="history-item">
        <span class="history-emoji">${e.emoji}</span>
        <span class="history-mood">${e.label}</span>
        <span class="history-date">${e.date}</span>
        <span class="history-date" style="opacity:0.6">${e.time}</span>
      </div>`).join('');
  } else {
    const recent = getMoodHistoryLocal().filter(e => e.ts >= Date.now() - 7 * 86400000);
    if (!recent.length) {
      container.innerHTML = '<p class="empty-msg">No check‑ins yet — how are you feeling today? 🌿</p>';
      return;
    }
    container.innerHTML = recent.map(e => `
      <div class="history-item">
        <span class="history-emoji">${e.emoji}</span>
        <span class="history-mood">${e.label}</span>
        <span class="history-date">${formatDate(e.ts)}</span>
        <span class="history-date" style="opacity:0.6">${formatTime(e.ts)}</span>
      </div>`).join('');
  }
}

function initMoodCheckin() {
  const moodBtns = els('.mood-btn');
  if (!moodBtns.length) return; // not on this page

  moodBtns.forEach(btn => {
    btn.addEventListener('click', async () => {
      const { mood, emoji, label } = btn.dataset;
      moodBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      applyMoodTheme(mood);
      showMoodMessage(mood);
      await saveMoodEntry(mood, label, emoji);
      renderHistory();
      if (mood === 'great' || mood === 'good') launchConfetti();
    });
  });

  renderHistory();
}

/* ============================================================
   BREATHING — only runs on breathe.html
   ============================================================ */
function initBreathing() {
  const startBtn = el('breathe-start');
  const stopBtn  = el('breathe-stop');
  const circle   = el('breathe-circle');
  const label    = el('breathe-label');
  if (!startBtn) return; // not on this page

  let breatheInterval = null;
  let breathePhase = 0;
  const PHASES = [
    { label: 'Breathe In',  cls: 'expand'  },
    { label: 'Hold',        cls: 'hold'    },
    { label: 'Breathe Out', cls: 'contract'},
  ];

  function runPhase() {
    const phase = PHASES[breathePhase % PHASES.length];
    circle.classList.remove('expand', 'hold', 'contract');
    void circle.offsetWidth;
    circle.classList.add(phase.cls);
    label.textContent = phase.label;
    breathePhase++;
  }

  startBtn.addEventListener('click', () => {
    if (breatheInterval) return;
    breathePhase = 0;
    runPhase();
    breatheInterval = setInterval(runPhase, 4000);
    startBtn.disabled = true;
    stopBtn.disabled  = false;
  });

  stopBtn.addEventListener('click', () => {
    clearInterval(breatheInterval);
    breatheInterval = null;
    circle.classList.remove('expand', 'hold', 'contract');
    label.textContent = 'Ready';
    startBtn.disabled = false;
    stopBtn.disabled  = true;
  });
}

/* ============================================================
   RESOURCES & FAVORITES — only runs on resources.html
   ============================================================ */
function getLocalFavs() {
  try { return JSON.parse(localStorage.getItem('ms_favorites')) || []; }
  catch { return []; }
}

async function toggleFavorite(resourceId) {
  if (!currentUser) {
    let favs = getLocalFavs();
    favs = favs.includes(resourceId) ? favs.filter(f => f !== resourceId) : [...favs, resourceId];
    localStorage.setItem('ms_favorites', JSON.stringify(favs));
  } else {
    await apiPost(`${API.resources}?action=favorite`, { resource_id: resourceId });
  }
  renderResources();
  renderFavorites();
}

async function renderResources() {
  const grid = el('resources-grid');
  if (!grid) return;

  const data = await apiGet(`${API.resources}?action=list`);
  const resources = data.resources || [];

  if (!resources.length) {
    grid.innerHTML = '<p class="empty-msg">No resources available yet.</p>';
    return;
  }

  grid.innerHTML = resources.map(r => `
    <div class="resource-card" id="card-${r.id}">
      <div class="resource-header">
        <span class="resource-icon">${r.icon}</span>
        <span class="resource-title">${r.title}</span>
        <button class="heart-btn" data-id="${r.id}">${r.is_favorite ? '❤️' : '🤍'}</button>
      </div>
      <p class="resource-desc">${r.description}</p>
      <a class="resource-link" href="${r.link}" target="_blank" rel="noopener">${r.link_text} ↗</a>
    </div>`).join('');

  grid.querySelectorAll('.heart-btn').forEach(btn => {
    btn.addEventListener('click', () => toggleFavorite(parseInt(btn.dataset.id)));
  });
}

async function renderFavorites() {
  const grid  = el('favorites-grid');
  const empty = el('favorites-empty');
  if (!grid) return;

  let favResources = [];
  if (currentUser) {
    const data = await apiGet(`${API.resources}?action=favorites`);
    favResources = data.favorites || [];
  } else {
    const favIds = getLocalFavs();
    if (favIds.length) {
      const data = await apiGet(`${API.resources}?action=list`);
      favResources = (data.resources || []).filter(r => favIds.includes(parseInt(r.id)));
    }
  }

  if (!favResources.length) {
    grid.innerHTML = '';
    if (empty) empty.style.display = 'block';
    return;
  }

  if (empty) empty.style.display = 'none';
  grid.innerHTML = favResources.map(r => `
    <div class="resource-card">
      <div class="resource-header">
        <span class="resource-icon">${r.icon}</span>
        <span class="resource-title">${r.title}</span>
        <button class="heart-btn" data-id="${r.id}">❤️</button>
      </div>
      <p class="resource-desc">${r.description}</p>
      <a class="resource-link" href="${r.link}" target="_blank" rel="noopener">${r.link_text} ↗</a>
    </div>`).join('');

  grid.querySelectorAll('.heart-btn').forEach(btn => {
    btn.addEventListener('click', () => toggleFavorite(parseInt(btn.dataset.id)));
  });
}

function initResources() {
  if (!el('resources-grid')) return;
  renderResources();
  renderFavorites();
}

/* ============================================================
   CONTACT FORM — only runs on contact.html
   ============================================================ */
function initContact() {
  const form = el('contact-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = form.querySelector('button[type=submit]');
    btn.disabled = true;
    btn.textContent = 'Sending...';

    const res = await apiPost(`${API.contact}?action=send`, {
      name:    el('cf-name').value,
      email:   el('cf-email').value,
      subject: el('cf-subject').value,
      message: el('cf-message').value,
    });

    const msgEl = el('cf-msg');
    msgEl.textContent = res.error || '✅ Message sent! We\'ll get back to you soon.';
    msgEl.className = 'mood-message show';
    if (!res.error) form.reset();
    btn.disabled = false;
    btn.textContent = 'Send Message';
    setTimeout(() => msgEl.classList.remove('show'), 5000);
  });
}

/* ============================================================
   CONFETTI — used on checkin.html
   ============================================================ */
const CONFETTI_COLORS = ['#4caf82','#81c784','#a5d6a7','#ffca28','#ff8a65','#ce93d8','#4fc3f7','#fff176'];

function launchConfetti() {
  const container = el('confetti-container');
  if (!container) return;
  for (let i = 0; i < 90; i++) {
    const piece = document.createElement('div');
    piece.className = 'confetti-piece';
    const size = Math.random() * 10 + 6;
    piece.style.cssText = `
      left:${Math.random()*100}vw;
      width:${size}px; height:${size}px;
      background:${CONFETTI_COLORS[Math.floor(Math.random()*CONFETTI_COLORS.length)]};
      border-radius:${Math.random()>0.5?'50%':'2px'};
      animation-duration:${Math.random()*2+2}s;
      animation-delay:${Math.random()*0.8}s;
      transform:rotate(${Math.random()*360}deg);`;
    container.appendChild(piece);
    piece.addEventListener('animationend', () => piece.remove());
  }
}

/* ============================================================
   AUTH — runs on every page
   ============================================================ */
function updateAuthUI() {
  const authBtn   = el('auth-btn');
  const userLabel = el('user-label');
  if (!authBtn) return;

  if (currentUser) {
    authBtn.textContent = 'Logout';
    authBtn.onclick     = handleLogout;
    if (userLabel) userLabel.textContent = `Hi, ${currentUser.name} 👋`;
  } else {
    authBtn.textContent = 'Login / Sign Up';
    authBtn.onclick     = () => {
      const modal = el('auth-modal');
      if (modal) modal.style.display = 'flex';
    };
    if (userLabel) userLabel.textContent = '';
  }
}

async function handleLogout() {
  await apiGet(`${API.auth}?action=logout`);
  currentUser = null;
  updateAuthUI();
  renderResources();
  renderFavorites();
  renderHistory();
}

function initAuth() {
  // Tab switching
  els('.auth-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      els('.auth-tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const loginPanel    = el('login-panel');
      const registerPanel = el('register-panel');
      if (loginPanel)    loginPanel.style.display    = tab.dataset.tab === 'login'    ? 'block' : 'none';
      if (registerPanel) registerPanel.style.display = tab.dataset.tab === 'register' ? 'block' : 'none';
    });
  });

  // Login form
  const loginForm = el('login-form');
  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const res = await apiPost(`${API.auth}?action=login`, {
        email:    el('login-email').value,
        password: el('login-password').value,
      });
      const errEl = el('login-error');
      if (res.error) {
        if (errEl) { errEl.textContent = res.error; errEl.style.display = 'block'; }
        return;
      }
      if (errEl) errEl.style.display = 'none';
      currentUser = res.user;
      const modal = el('auth-modal');
      if (modal) modal.style.display = 'none';
      updateAuthUI();
      renderResources();
      renderFavorites();
      renderHistory();
    });
  }

  // Register form
  const registerForm = el('register-form');
  if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const res = await apiPost(`${API.auth}?action=register`, {
        name:     el('reg-name').value,
        email:    el('reg-email').value,
        password: el('reg-password').value,
        confirm:  el('reg-confirm').value,
      });
      const errEl = el('register-error');
      if (res.error) {
        if (errEl) { errEl.textContent = res.error; errEl.style.display = 'block'; }
        return;
      }
      if (errEl) errEl.style.display = 'none';
      currentUser = res.user;
      const modal = el('auth-modal');
      if (modal) modal.style.display = 'none';
      updateAuthUI();
      renderResources();
      renderFavorites();
      renderHistory();
    });
  }

  // Close modal on backdrop click
  const authModal = el('auth-modal');
  if (authModal) {
    authModal.addEventListener('click', e => {
      if (e.target === authModal) authModal.style.display = 'none';
    });
  }
}

/* ============================================================
   HAMBURGER — runs on every page
   ============================================================ */
function initHamburger() {
  const hamburger = el('hamburger');
  if (!hamburger) return;
  hamburger.addEventListener('click', () => {
    const navLinks = el('nav-links');
    if (navLinks) navLinks.classList.toggle('open');
  });
  els('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
      const navLinks = el('nav-links');
      if (navLinks) navLinks.classList.remove('open');
    });
  });
}

/* ============================================================
   INIT — runs on every page, calls only what's needed
   ============================================================ */
async function init() {
  // Check session on every page
  const session = await apiGet(`${API.auth}?action=me`);
  if (session.loggedIn) currentUser = session.user;

  // Auth UI runs everywhere
  updateAuthUI();
  initAuth();
  initHamburger();

  // Page-specific — each checks if its elements exist
  initAffirmation();
  initMoodCheckin();
  initBreathing();
  initResources();
  initContact();
}

document.addEventListener('DOMContentLoaded', init);
