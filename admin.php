<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>MindfulSpace — Admin Panel</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <style>
    :root{--accent:#4caf82;--dark:#1a2e24;--muted:#5a7a6a;--bg:#f0f7f4;--card:#fff;--border:#e0ede7;--danger:#e53935;--warning:#f57c00;}
    *{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--dark);min-height:100vh;}
    /* ---- Layout ---- */
    #login-screen{display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px;}
    #app{display:none;}
    .sidebar{position:fixed;top:0;left:0;width:230px;height:100vh;background:var(--dark);color:#fff;padding:28px 20px;display:flex;flex-direction:column;gap:6px;}
    .sidebar-logo{font-size:1.1rem;font-weight:700;color:var(--accent);margin-bottom:20px;}
    .nav-btn{background:none;border:none;color:rgba(255,255,255,0.7);font-family:'Poppins',sans-serif;font-size:0.88rem;font-weight:500;padding:10px 14px;border-radius:10px;cursor:pointer;text-align:left;width:100%;transition:background 0.2s,color 0.2s;}
    .nav-btn:hover,.nav-btn.active{background:rgba(76,175,130,0.2);color:#fff;}
    .main{margin-left:230px;padding:36px 40px;min-height:100vh;}
    /* ---- Cards ---- */
    .card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:28px;margin-bottom:24px;}
    h1{font-size:1.6rem;font-weight:700;margin-bottom:6px;}
    h2{font-size:1.15rem;font-weight:700;margin-bottom:18px;}
    .sub{color:var(--muted);font-size:0.88rem;margin-bottom:24px;}
    /* ---- Forms ---- */
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    .form-group{display:flex;flex-direction:column;gap:6px;}
    .form-group.full{grid-column:1/-1;}
    label{font-size:0.8rem;font-weight:600;color:var(--muted);}
    input,textarea,select{font-family:'Poppins',sans-serif;font-size:0.9rem;padding:10px 14px;border:1.5px solid var(--border);border-radius:10px;background:#fafffe;outline:none;transition:border 0.2s;width:100%;}
    input:focus,textarea:focus,select:focus{border-color:var(--accent);}
    textarea{resize:vertical;min-height:90px;}
    /* ---- Buttons ---- */
    .btn{font-family:'Poppins',sans-serif;font-size:0.88rem;font-weight:600;padding:10px 22px;border-radius:30px;border:none;cursor:pointer;transition:opacity 0.2s,transform 0.15s;}
    .btn:hover{opacity:0.88;transform:translateY(-1px);}
    .btn-green{background:var(--accent);color:#fff;}
    .btn-red{background:var(--danger);color:#fff;}
    .btn-ghost{background:transparent;border:1.5px solid var(--border);color:var(--muted);}
    .btn-sm{padding:6px 14px;font-size:0.8rem;}
    /* ---- Table ---- */
    table{width:100%;border-collapse:collapse;font-size:0.88rem;}
    th{text-align:left;padding:10px 14px;font-size:0.78rem;font-weight:600;color:var(--muted);border-bottom:2px solid var(--border);}
    td{padding:12px 14px;border-bottom:1px solid var(--border);vertical-align:top;}
    tr:last-child td{border-bottom:none;}
    tr:hover td{background:#f7fdf9;}
    /* ---- Badges ---- */
    .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;}
    .badge-green{background:#e8f5e9;color:#2e7d52;}
    .badge-red{background:#fde8e8;color:#c62828;}
    .badge-orange{background:#fff3e0;color:#e65100;}
    /* ---- Alert ---- */
    .alert{padding:12px 18px;border-radius:10px;font-size:0.88rem;margin-bottom:16px;display:none;}
    .alert-success{background:#e8f5e9;color:#2e7d52;border:1px solid #a5d6a7;}
    .alert-error{background:#fde8e8;color:#c62828;border:1px solid #ef9a9a;}
    /* ---- Stat boxes ---- */
    .stat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;}
    .stat-box{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px 22px;}
    .stat-num{font-size:2rem;font-weight:800;color:var(--accent);}
    .stat-lbl{font-size:0.82rem;color:var(--muted);margin-top:4px;}
    /* ---- Login form ---- */
    .login-card{background:#fff;border:1px solid var(--border);border-radius:20px;padding:44px 40px;width:100%;max-width:420px;box-shadow:0 8px 32px rgba(60,120,80,0.10);}
    .login-logo{font-size:1.3rem;font-weight:800;color:var(--accent);margin-bottom:8px;}
    .login-sub{color:var(--muted);font-size:0.88rem;margin-bottom:28px;}
    /* ---- Tabs ---- */
    .page{display:none;}
    .page.active{display:block;}
    /* ---- Mood wall posts ---- */
    .post-card{background:#f7fdf9;border:1px solid var(--border);border-radius:12px;padding:14px 18px;margin-bottom:10px;}
    .post-meta{font-size:0.78rem;color:var(--muted);margin-top:6px;}
  </style>
</head>
<body>

<!-- LOGIN SCREEN -->
<div id="login-screen">
  <div class="login-card">
    <div class="login-logo">🌿 MindfulSpace</div>
    <p class="login-sub">Admin Panel — Please sign in</p>
    <div id="login-alert" class="alert alert-error"></div>
    <div style="display:flex;flex-direction:column;gap:14px;">
      <div class="form-group">
        <label>Email</label>
        <input type="email" id="login-email" placeholder="admin@mindfulspace.pk"/>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" id="login-password" placeholder="••••••••"/>
      </div>
      <button class="btn btn-green" onclick="adminLogin()">Sign In</button>
    </div>
  </div>
</div>

<!-- MAIN APP -->
<div id="app">
  <div class="sidebar">
    <div class="sidebar-logo">🌿 MindfulSpace</div>
    <button class="nav-btn active" onclick="showPage('dashboard')">📊 Dashboard</button>
    <button class="nav-btn" onclick="showPage('resources')">📚 Resources</button>
    <button class="nav-btn" onclick="showPage('messages')">✉️ Messages</button>
    <button class="nav-btn" onclick="showPage('wall')">🌍 Mood Wall</button>
    <div style="margin-top:auto;">
      <button class="nav-btn" onclick="logout()" style="color:#ef9a9a;">🚪 Logout</button>
    </div>
  </div>

  <div class="main">

    <!-- DASHBOARD -->
    <div class="page active" id="page-dashboard">
      <h1>Dashboard</h1>
      <p class="sub">Welcome back, Admin. Here's what's happening on MindfulSpace.</p>
      <div class="stat-row" id="stat-row">
        <div class="stat-box"><div class="stat-num" id="stat-resources">—</div><div class="stat-lbl">Active Resources</div></div>
        <div class="stat-box"><div class="stat-num" id="stat-messages">—</div><div class="stat-lbl">Unread Messages</div></div>
        <div class="stat-box"><div class="stat-num" id="stat-wall">—</div><div class="stat-lbl">Mood Wall Posts (24h)</div></div>
        <div class="stat-box"><div class="stat-num" id="stat-moods">—</div><div class="stat-lbl">Total Mood Check-Ins</div></div>
      </div>
      <div class="card">
        <h2>Community Mood (Last 24 hours)</h2>
        <div id="mood-stats-display"></div>
      </div>
    </div>

    <!-- RESOURCES -->
    <div class="page" id="page-resources">
      <h1>Resources</h1>
      <p class="sub">Add, edit, or remove mental health resources shown on the website.</p>

      <!-- Add form -->
      <div class="card">
        <h2>➕ Add New Resource</h2>
        <div id="res-alert" class="alert"></div>
        <div class="form-grid">
          <div class="form-group">
            <label>Icon (emoji)</label>
            <input id="res-icon" value="💚" maxlength="5"/>
          </div>
          <div class="form-group">
            <label>Link Text</label>
            <input id="res-link-text" placeholder="Visit website"/>
          </div>
          <div class="form-group full">
            <label>Title</label>
            <input id="res-title" placeholder="Resource name"/>
          </div>
          <div class="form-group full">
            <label>Description</label>
            <textarea id="res-desc" placeholder="Brief description..."></textarea>
          </div>
          <div class="form-group full">
            <label>URL</label>
            <input id="res-link" placeholder="https://..."/>
          </div>
        </div>
        <div style="margin-top:16px;">
          <button class="btn btn-green" onclick="addResource()">Add Resource</button>
        </div>
      </div>

      <!-- Resources table -->
      <div class="card">
        <h2>All Resources</h2>
        <table>
          <thead><tr><th>Icon</th><th>Title</th><th>Link</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="resources-table-body"></tbody>
        </table>
      </div>
    </div>

    <!-- MESSAGES -->
    <div class="page" id="page-messages">
      <h1>Contact Messages</h1>
      <p class="sub">Messages sent through the website contact form.</p>
      <div class="card">
        <div id="messages-list"></div>
      </div>
    </div>

    <!-- MOOD WALL -->
    <div class="page" id="page-wall">
      <h1>Public Mood Wall</h1>
      <p class="sub">Anonymous mood posts from the community.</p>
      <div class="card">
        <div id="wall-list"></div>
      </div>
    </div>

  </div>
</div>

<script>
const API = {
  auth:      'auth.php',
  resources: 'resources.php',
  contact:   'contact.php',
  wall:      'wall.php',
  mood:      'mood.php',
};

async function post(url, data) {
  const r = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data) });
  return r.json();
}
async function get(url) {
  const r = await fetch(url);
  return r.json();
}

function showAlert(id, msg, type='error') {
  const el = document.getElementById(id);
  el.className = `alert alert-${type}`;
  el.textContent = msg;
  el.style.display = 'block';
  setTimeout(() => el.style.display = 'none', 4000);
}

// ---- Login ----
async function adminLogin() {
  const email    = document.getElementById('login-email').value;
  const password = document.getElementById('login-password').value;
  const res = await post(`${API.auth}?action=login`, { email, password });
  if (res.error) { showAlert('login-alert', res.error); return; }
  if (res.user?.role !== 'admin') { showAlert('login-alert', 'Access denied. Admins only.'); return; }
  document.getElementById('login-screen').style.display = 'none';
  document.getElementById('app').style.display = 'block';
  loadDashboard();
}

async function logout() {
  await get(`${API.auth}?action=logout`);
  location.reload();
}

// ---- Pages ----
function showPage(name) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
  document.getElementById(`page-${name}`).classList.add('active');
  event.currentTarget.classList.add('active');
  if (name === 'resources') loadResources();
  if (name === 'messages')  loadMessages();
  if (name === 'wall')      loadWall();
}

// ---- Dashboard ----
async function loadDashboard() {
  const [resData, msgData, wallData] = await Promise.all([
    get(`${API.resources}?action=admin_list`),
    get(`${API.contact}?action=list`),
    get(`${API.wall}?action=stats`),
  ]);

  const activeRes  = (resData.resources || []).filter(r => r.is_active == 1).length;
  const unreadMsg  = (msgData.messages  || []).filter(m => !m.is_read).length;
  const wallTotal  = (wallData.stats    || []).reduce((a, s) => a + parseInt(s.count), 0);

  document.getElementById('stat-resources').textContent = activeRes;
  document.getElementById('stat-messages').textContent  = unreadMsg;
  document.getElementById('stat-wall').textContent      = wallTotal;
  document.getElementById('stat-moods').textContent     = '—';

  const moodEl = document.getElementById('mood-stats-display');
  if (!wallData.stats?.length) { moodEl.innerHTML = '<p style="color:var(--muted)">No mood wall activity in the last 24 hours.</p>'; return; }
  moodEl.innerHTML = wallData.stats.map(s =>
    `<div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
       <span style="font-size:1.6rem">${s.emoji}</span>
       <div style="flex:1;background:#e8f5e9;border-radius:30px;height:10px;">
         <div style="background:var(--accent);border-radius:30px;height:10px;width:${Math.min(100, s.count * 10)}%"></div>
       </div>
       <span style="font-size:0.85rem;font-weight:600;color:var(--muted)">${s.mood} (${s.count})</span>
     </div>`
  ).join('');
}

// ---- Resources ----
async function loadResources() {
  const data = await get(`${API.resources}?action=admin_list`);
  const tbody = document.getElementById('resources-table-body');
  if (!data.resources?.length) { tbody.innerHTML = '<tr><td colspan="5" style="color:var(--muted)">No resources yet.</td></tr>'; return; }
  tbody.innerHTML = data.resources.map(r => `
    <tr>
      <td>${r.icon}</td>
      <td><strong>${r.title}</strong></td>
      <td><a href="${r.link}" target="_blank" style="color:var(--accent);font-size:0.82rem">${r.link_text}</a></td>
      <td><span class="badge ${r.is_active == 1 ? 'badge-green' : 'badge-red'}">${r.is_active == 1 ? 'Active' : 'Hidden'}</span></td>
      <td style="display:flex;gap:8px;flex-wrap:wrap;">
        <button class="btn btn-ghost btn-sm" onclick="toggleActive(${r.id}, ${r.is_active})">${r.is_active == 1 ? 'Hide' : 'Show'}</button>
        <button class="btn btn-red btn-sm" onclick="deleteResource(${r.id})">Delete</button>
      </td>
    </tr>
  `).join('');
}

async function addResource() {
  const data = {
    icon: document.getElementById('res-icon').value,
    title: document.getElementById('res-title').value,
    description: document.getElementById('res-desc').value,
    link: document.getElementById('res-link').value,
    link_text: document.getElementById('res-link-text').value,
  };
  const res = await post(`${API.resources}?action=add`, data);
  if (res.error) { showAlert('res-alert', res.error); return; }
  showAlert('res-alert', 'Resource added successfully!', 'success');
  document.getElementById('res-title').value = '';
  document.getElementById('res-desc').value  = '';
  document.getElementById('res-link').value  = '';
  document.getElementById('res-link-text').value = '';
  loadResources();
}

async function toggleActive(id, current) {
  await post(`${API.resources}?action=edit`, { id, is_active: current == 1 ? 0 : 1 });
  loadResources();
}

async function deleteResource(id) {
  if (!confirm('Delete this resource permanently?')) return;
  await post(`${API.resources}?action=delete`, { id });
  loadResources();
}

// ---- Messages ----
async function loadMessages() {
  const data = await get(`${API.contact}?action=list`);
  const el   = document.getElementById('messages-list');
  if (!data.messages?.length) { el.innerHTML = '<p style="color:var(--muted)">No messages yet.</p>'; return; }
  el.innerHTML = data.messages.map(m => `
    <div style="border:1px solid var(--border);border-radius:12px;padding:18px 20px;margin-bottom:12px;background:${m.is_read ? '#fff' : '#f0faf4'}">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
        <div>
          <strong>${m.name}</strong> <span style="color:var(--muted);font-size:0.82rem">&lt;${m.email}&gt;</span>
          ${!m.is_read ? '<span class="badge badge-green" style="margin-left:8px">New</span>' : ''}
        </div>
        <span style="font-size:0.78rem;color:var(--muted)">${m.received_at}</span>
      </div>
      <div style="font-weight:600;margin:8px 0 4px"><strong>Subject:</strong> ${m.subject}</div>
      <div style="font-size:0.88rem;color:var(--muted);line-height:1.6">${m.message}</div>
      ${!m.is_read ? `<button class="btn btn-ghost btn-sm" style="margin-top:10px" onclick="markRead(${m.id})">Mark as Read</button>` : ''}
    </div>
  `).join('');
}

async function markRead(id) {
  await post(`${API.contact}?action=read`, { id });
  loadMessages();
}

// ---- Mood Wall ----
async function loadWall() {
  const data = await get(`${API.wall}?action=feed&limit=30`);
  const el   = document.getElementById('wall-list');
  if (!data.posts?.length) { el.innerHTML = '<p style="color:var(--muted)">No mood wall posts yet.</p>'; return; }
  el.innerHTML = data.posts.map(p => `
    <div class="post-card">
      <span style="font-size:1.5rem">${p.emoji}</span>
      <strong style="margin-left:8px">${p.mood}</strong>
      ${p.message ? `<p style="margin-top:6px;font-size:0.9rem">${p.message}</p>` : ''}
      <div class="post-meta">${p.posted_at}</div>
    </div>
  `).join('');
}
</script>
</body>
</html>
