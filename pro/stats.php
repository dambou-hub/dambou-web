<?php
// Fichier en ASCII uniquement (contrainte Hostinger).
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Statistiques - Dambou Pro</title>
<meta name="theme-color" content="#00BFA5">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<style>
  :root {
    --primary: #00BFA5; --primary-dark: #00897B; --text-dark: #2D3748; --text-medium: #718096;
    --text-light: #A0AEC0; --background: #F7F8FA; --card-border: #E2E8F0; --warning: #DD6B20; --error: #E53E3E;
    --c-caisse: #52B788; --c-commandes: #F4A261; --c-rdv: #00BFA5;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--background); color: var(--text-dark); min-height: 100vh; }
  .topbar { background: white; border-bottom: 1px solid var(--card-border); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; text-decoration: none; color: var(--text-dark); }
  .brand img { height: 26px; width: auto; }
  .back-link { font-size: 13px; color: var(--text-medium); text-decoration: none; font-weight: 600; }
  .back-link:hover { color: var(--primary); }

  .container { max-width: 720px; margin: 0 auto; padding: 24px 24px 60px; }
  #loading { text-align: center; padding: 60px 20px; color: var(--text-medium); }

  .period-tabs { display: flex; gap: 4px; background: white; border: 1px solid var(--card-border); border-radius: 12px; padding: 4px; margin-bottom: 20px; }
  .period-btn { flex: 1; padding: 10px; border: none; background: none; font-family: inherit; font-size: 13px; font-weight: 700; color: var(--text-medium); border-radius: 8px; cursor: pointer; }
  .period-btn.active { background: var(--primary); color: white; }

  .total-card { background: linear-gradient(160deg, var(--primary), var(--primary-dark)); border-radius: 20px; padding: 26px; color: white; margin-bottom: 20px; }
  .total-card .label { font-size: 13px; opacity: 0.85; margin-bottom: 4px; }
  .total-card .value { font-size: 38px; font-weight: 900; }

  .chart-card { background: white; border: 1px solid var(--card-border); border-radius: 16px; padding: 18px; margin-bottom: 16px; }
  .chart-card h3 { font-size: 13px; font-weight: 800; color: var(--text-medium); margin-bottom: 14px; }
  .charts-row { display: grid; grid-template-columns: 1fr; gap: 16px; }
  @media (min-width: 720px) { .charts-row.two-col { grid-template-columns: 1.4fr 1fr; align-items: start; } }
  .chart-canvas-wrap { position: relative; height: 220px; }
  .chart-canvas-wrap.donut { height: 200px; }

  .warning-banner { background: rgba(221,107,32,0.08); border: 1px solid rgba(221,107,32,0.25); border-radius: 12px; padding: 12px 14px; font-size: 12px; color: #a3591e; margin-bottom: 16px; }

  .source-card { background: white; border: 1px solid var(--card-border); border-radius: 16px; padding: 18px; margin-bottom: 12px; }
  .source-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; }
  .source-name { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; }
  .source-dot { width: 10px; height: 10px; border-radius: 50%; }
  .source-value { font-size: 18px; font-weight: 900; }
  .source-sub { font-size: 12px; color: var(--text-light); margin-bottom: 10px; }
  .mode-row { display: flex; justify-content: space-between; font-size: 12px; color: var(--text-medium); padding: 5px 0; border-top: 1px solid var(--card-border); }
  .mode-row:first-of-type { border-top: none; }

  .section-title { font-size: 13px; font-weight: 800; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; margin: 22px 0 10px; }
  .product-row { display: flex; align-items: center; justify-content: space-between; background: white; border: 1px solid var(--card-border); border-radius: 12px; padding: 12px 14px; margin-bottom: 8px; }
  .product-rank { width: 22px; height: 22px; border-radius: 50%; background: rgba(0,191,165,0.1); color: var(--primary-dark); font-size: 11px; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .product-name { font-size: 13px; font-weight: 700; flex: 1; margin: 0 10px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .product-qty { font-size: 12px; color: var(--text-medium); margin-right: 10px; }
  .product-revenue { font-size: 13px; font-weight: 800; color: var(--primary-dark); }
  #empty-products { text-align: center; padding: 20px; color: var(--text-light); font-size: 13px; }
</style>
</head>
<body>
  <div class="topbar">
    <a class="brand" href="/pro"><img src="/assets/icon.png" alt=""> Dambou Pro</a>
    <a class="back-link" href="/pro">Retour au tableau de bord</a>
  </div>

  <div class="container">
    <div class="period-tabs">
      <button class="period-btn active" data-period="0">Auj.</button>
      <button class="period-btn" data-period="1">Sem.</button>
      <button class="period-btn" data-period="2">Mois</button>
      <button class="period-btn" data-period="3">Annee</button>
    </div>

    <div id="loading">Chargement des statistiques...</div>
    <div id="content" style="display:none"></div>
  </div>

  <script type="module">
    import { requireAuth, getBusinessForUser } from '/pro/js/auth.js';
    import { loadStats } from '/pro/js/stats.js';

    let business = null;
    let periodIndex = 0;

    function escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str || '';
      return div.innerHTML;
    }
    function currencySymbol() {
      return { EUR: '\u20ac', MAD: 'DH', CHF: 'CHF', XOF: 'FCFA' }[(business && business.currency_code) || 'EUR'] || '\u20ac';
    }
    function fmt(n) { return Math.round(n || 0) + ' ' + currencySymbol(); }

    document.querySelectorAll('.period-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.period-btn').forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');
        periodIndex = parseInt(btn.dataset.period, 10);
        loadAndRender();
      });
    });

    async function loadAndRender() {
      document.getElementById('loading').style.display = 'block';
      document.getElementById('content').style.display = 'none';

      const stats = await loadStats(business.id, periodIndex);

      const container = document.getElementById('content');
      container.innerHTML = '';

      if (stats.errors && stats.errors.length) {
        const errBanner = document.createElement('div');
        errBanner.className = 'warning-banner';
        errBanner.style.background = 'rgba(229,62,62,0.08)';
        errBanner.style.borderColor = 'rgba(229,62,62,0.25)';
        errBanner.style.color = 'var(--error)';
        errBanner.innerHTML = '<strong>Erreur de chargement</strong> (voir la console) :<br>' + stats.errors.map(escapeHtml).join('<br>');
        container.appendChild(errBanner);
      }

      const totalCard = document.createElement('div');
      totalCard.className = 'total-card';
      totalCard.innerHTML = '<div class="label">Total encaisse</div><div class="value">' + fmt(stats.totalGlobal) + '</div>';
      container.appendChild(totalCard);

      if (stats.nbPaidNotDone > 0) {
        const warn = document.createElement('div');
        warn.className = 'warning-banner';
        warn.textContent = stats.nbPaidNotDone + ' commande(s) payee(s) en ligne (' + fmt(stats.totalPaidNotDone) + ') n\'ont pas encore le statut "terminee". Verifiez qu\'elles ont bien ete preparees.';
        container.appendChild(warn);
      }

      renderCharts(container, stats);

      container.appendChild(sourceCard('Caisse sur place', 'var(--c-caisse)', stats.totalCaisse, null, stats.caisseByMode));
      container.appendChild(sourceCard('Commandes Dambou', 'var(--c-commandes)', stats.totalCommandes, stats.nbCommandes + ' commande(s)', null));
      container.appendChild(sourceCard('Reservations', 'var(--c-rdv)', stats.totalRdv, stats.nbRdv + ' RDV confirme(s)' + (stats.rdvEnLigne > 0 ? ' - dont ' + fmt(stats.rdvEnLigne) + ' payes en ligne' : ''), stats.rdvSurPlaceByMode));

      const secTitle = document.createElement('div');
      secTitle.className = 'section-title';
      secTitle.textContent = 'Articles les plus vendus';
      container.appendChild(secTitle);

      if (stats.topProducts.length === 0) {
        container.innerHTML += '<div id="empty-products">Aucune vente sur cette periode.</div>';
      } else {
        stats.topProducts.slice(0, 10).forEach((p, i) => {
          const row = document.createElement('div');
          row.className = 'product-row';
          row.innerHTML =
            '<div class="product-rank">' + (i + 1) + '</div>' +
            '<div class="product-name">' + escapeHtml(p.name) + '</div>' +
            '<div class="product-qty">x' + p.qty + '</div>' +
            '<div class="product-revenue">' + fmt(p.revenue) + '</div>';
          container.appendChild(row);
        });
      }

      document.getElementById('loading').style.display = 'none';
      container.style.display = 'block';
    }

    function sourceCard(name, color, total, sub, byMode) {
      const card = document.createElement('div');
      card.className = 'source-card';
      let html = '<div class="source-head"><div class="source-name"><span class="source-dot" style="background:' + color + '"></span>' + escapeHtml(name) + '</div>' +
        '<div class="source-value" style="color:' + color + '">' + fmt(total) + '</div></div>';
      if (sub) html += '<div class="source-sub">' + escapeHtml(sub) + '</div>';
      if (byMode && Object.keys(byMode).length) {
        html += Object.keys(byMode).map((mode) =>
          '<div class="mode-row"><span>' + escapeHtml(mode) + '</span><span>' + fmt(byMode[mode]) + '</span></div>'
        ).join('');
      }
      card.innerHTML = html;
      return card;
    }

    let trendChartInstance = null;
    let donutChartInstance = null;

    function renderCharts(container, stats) {
      const hasTrend = stats.trend && stats.trend.length > 0;
      const hasBreakdown = stats.totalGlobal > 0;
      if (!hasTrend && !hasBreakdown) return;

      const row = document.createElement('div');
      row.className = 'charts-row' + (hasTrend && hasBreakdown ? ' two-col' : '');

      if (hasTrend) {
        const card = document.createElement('div');
        card.className = 'chart-card';
        card.innerHTML = '<h3>Evolution du chiffre d\'affaires</h3><div class="chart-canvas-wrap"><canvas id="trend-chart"></canvas></div>';
        row.appendChild(card);
      }
      if (hasBreakdown) {
        const card = document.createElement('div');
        card.className = 'chart-card';
        card.innerHTML = '<h3>Repartition par source</h3><div class="chart-canvas-wrap donut"><canvas id="donut-chart"></canvas></div>';
        row.appendChild(card);
      }
      container.appendChild(row);

      if (trendChartInstance) { trendChartInstance.destroy(); trendChartInstance = null; }
      if (donutChartInstance) { donutChartInstance.destroy(); donutChartInstance = null; }

      if (hasTrend) {
        const ctx = document.getElementById('trend-chart').getContext('2d');
        trendChartInstance = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: stats.trend.map((t) => t.label),
            datasets: [{
              data: stats.trend.map((t) => t.total),
              backgroundColor: 'rgba(0,191,165,0.55)',
              borderRadius: 4,
              maxBarThickness: 28,
            }],
          },
          options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => fmt(c.parsed.y) } } },
            scales: {
              y: { beginAtZero: true, grid: { color: '#F0F2F5' }, ticks: { font: { family: 'Inter', size: 10 } } },
              x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 10 } } },
            },
          },
        });
      }

      if (hasBreakdown) {
        const ctx2 = document.getElementById('donut-chart').getContext('2d');
        donutChartInstance = new Chart(ctx2, {
          type: 'doughnut',
          data: {
            labels: ['Caisse sur place', 'Commandes Dambou', 'Reservations'],
            datasets: [{
              data: [stats.totalCaisse, stats.totalCommandes, stats.totalRdv],
              backgroundColor: ['#52B788', '#F4A261', '#00BFA5'],
              borderWidth: 0,
            }],
          },
          options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
              legend: { position: 'bottom', labels: { font: { family: 'Inter', size: 11 }, padding: 12, boxWidth: 10 } },
              tooltip: { callbacks: { label: (c) => c.label + ': ' + fmt(c.parsed) } },
            },
          },
        });
      }
    }

    (async () => {
      const session = await requireAuth();
      if (!session) return;
      business = await getBusinessForUser(session.user.id);
      if (!business) {
        document.getElementById('loading').textContent = 'Aucun etablissement associe a ce compte.';
        return;
      }
      await loadAndRender();
    })();
  </script>
</body>
</html>
