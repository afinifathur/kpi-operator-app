import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

// Data disediakan dari Blade via window.DASHBOARD_DATA
const el = document.getElementById('trendChart');
if (el && window.DASHBOARD_DATA) {
  const ctx = el.getContext('2d');
  const { labels, values } = window.DASHBOARD_DATA;

  new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: '% Pencapaian (harian)',
        data: values,
        fill: false,
        tension: 0.25,
        pointRadius: 3,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: { beginAtZero: true, suggestedMax: 150, title: { display: true, text: '%' } },
        x: { ticks: { autoSkip: true, maxTicksLimit: 10 } },
      },
      plugins: {
        legend: { display: true },
        tooltip: { enabled: true },
      },
    },
  });
}
