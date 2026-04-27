import * as fs from 'fs';
import * as path from 'path';

interface BugReport {
  id: string;
  severity: 'critical' | 'high' | 'medium' | 'low' | 'info';
  category: string;
  title: string;
  description: string;
  timestamp: string;
  testFile?: string;
  screenshot?: string;
}

interface TestMetrics {
  totalTests: number;
  passed: number;
  failed: number;
  skipped: number;
  duration: number;
  startTime: string;
  endTime: string;
}

export class AdvancedReporter {
  private bugs: BugReport[] = [];
  private metrics: TestMetrics = {
    totalTests: 0,
    passed: 0,
    failed: 0,
    skipped: 0,
    duration: 0,
    startTime: '',
    endTime: ''
  };

  constructor(private outputDir: string = 'test-results') {
    this.ensureOutputDir();
  }

  private ensureOutputDir() {
    if (!fs.existsSync(this.outputDir)) {
      fs.mkdirSync(this.outputDir, { recursive: true });
    }
  }

  addBug(bug: Omit<BugReport, 'id' | 'timestamp'>) {
    this.bugs.push({
      ...bug,
      id: `BUG-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
      timestamp: new Date().toLocaleString('es-CL')
    });
  }

  updateMetrics(metrics: Partial<TestMetrics>) {
    this.metrics = { ...this.metrics, ...metrics };
  }

  generateReport() {
    const html = this.generateHTML();
    const reportPath = path.join(this.outputDir, 'audit-report.html');
    fs.writeFileSync(reportPath, html);
    
    // También generar JSON para integración CI/CD
    const jsonPath = path.join(this.outputDir, 'audit-report.json');
    fs.writeFileSync(jsonPath, JSON.stringify({
      bugs: this.bugs,
      metrics: this.metrics,
      summary: this.getSummary()
    }, null, 2));
    
    return reportPath;
  }

  private getSummary() {
    const bySeverity = {
      critical: this.bugs.filter(b => b.severity === 'critical').length,
      high: this.bugs.filter(b => b.severity === 'high').length,
      medium: this.bugs.filter(b => b.severity === 'medium').length,
      low: this.bugs.filter(b => b.severity === 'low').length,
      info: this.bugs.filter(b => b.severity === 'info').length,
    };

    const byCategory = this.bugs.reduce((acc, bug) => {
      acc[bug.category] = (acc[bug.category] || 0) + 1;
      return acc;
    }, {} as Record<string, number>);

    return { bySeverity, byCategory };
  }

  private generateHTML(): string {
    const summary = this.getSummary();
    
    return `<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Torque Studio - Reporte de Auditoría Avanzado</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: linear-gradient(135deg, #0f1419 0%, #1a1d26 100%);
      color: #e8eaf2;
      line-height: 1.6;
      min-height: 100vh;
    }
    .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
    
    /* Header */
    .header {
      text-align: center;
      padding: 40px 20px;
      background: linear-gradient(135deg, rgba(138,180,248,0.1) 0%, rgba(77,142,255,0.1) 100%);
      border-radius: 16px;
      margin-bottom: 30px;
      border: 1px solid rgba(138,180,248,0.2);
    }
    .header h1 {
      font-size: 2.5rem;
      background: linear-gradient(135deg, #8ab4f8, #4d8eff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 10px;
    }
    .header p { color: #9aa3b2; font-size: 1.1rem; }
    
    /* Metrics Grid */
    .metrics-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .metric-card {
      background: rgba(255,255,255,0.05);
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      border: 1px solid rgba(255,255,255,0.1);
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .metric-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    }
    .metric-value {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 5px;
    }
    .metric-label { color: #9aa3b2; font-size: 0.9rem; }
    
    /* Severity Colors */
    .critical { color: #dc2626; }
    .high { color: #ea580c; }
    .medium { color: #ca8a04; }
    .low { color: #16a34a; }
    .info { color: #2563eb; }
    .success { color: #22c55e; }
    
    /* Charts Section */
    .charts-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    .chart-card {
      background: rgba(255,255,255,0.03);
      border-radius: 12px;
      padding: 20px;
      border: 1px solid rgba(255,255,255,0.1);
    }
    .chart-card h3 {
      margin-bottom: 15px;
      font-size: 1.1rem;
      color: #e8eaf2;
    }
    
    /* Filters */
    .filters {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 20px;
      padding: 15px;
      background: rgba(255,255,255,0.03);
      border-radius: 12px;
    }
    .filter-btn {
      padding: 8px 16px;
      border: 1px solid rgba(255,255,255,0.2);
      background: transparent;
      color: #9aa3b2;
      border-radius: 20px;
      cursor: pointer;
      transition: all 0.2s;
      font-size: 0.9rem;
    }
    .filter-btn:hover, .filter-btn.active {
      background: rgba(77,142,255,0.3);
      border-color: #4d8eff;
      color: #e8eaf2;
    }
    
    /* Search */
    .search-box {
      width: 100%;
      padding: 12px 16px;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 8px;
      color: #e8eaf2;
      font-size: 1rem;
      margin-bottom: 20px;
    }
    .search-box::placeholder { color: #6b7280; }
    .search-box:focus {
      outline: none;
      border-color: #4d8eff;
    }
    
    /* Bug List */
    .bug-list { margin-top: 20px; }
    .bug-item {
      background: rgba(255,255,255,0.03);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 15px;
      border-left: 4px solid;
      transition: all 0.2s;
    }
    .bug-item:hover {
      background: rgba(255,255,255,0.05);
      transform: translateX(4px);
    }
    .bug-item.critical { border-color: #dc2626; }
    .bug-item.high { border-color: #ea580c; }
    .bug-item.medium { border-color: #ca8a04; }
    .bug-item.low { border-color: #16a34a; }
    
    .bug-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 10px;
      flex-wrap: wrap;
      gap: 10px;
    }
    .bug-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: #e8eaf2;
    }
    .bug-id {
      font-family: monospace;
      font-size: 0.8rem;
      color: #6b7280;
    }
    .bug-meta {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 10px;
    }
    .badge {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 500;
      text-transform: uppercase;
    }
    .badge-critical { background: rgba(220,38,38,0.2); color: #fca5a5; }
    .badge-high { background: rgba(234,88,12,0.2); color: #fdba74; }
    .badge-medium { background: rgba(202,138,4,0.2); color: #fcd34d; }
    .badge-low { background: rgba(22,163,74,0.2); color: #86efac; }
    
    .bug-description {
      color: #9aa3b2;
      margin-top: 10px;
      line-height: 1.5;
    }
    
    /* Export Buttons */
    .export-section {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .export-btn {
      padding: 10px 20px;
      background: linear-gradient(135deg, #4d8eff, #6b8cff);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.2s;
    }
    .export-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(77,142,255,0.3);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
      .header h1 { font-size: 1.8rem; }
      .charts-grid { grid-template-columns: 1fr; }
      .metrics-grid { grid-template-columns: repeat(2, 1fr); }
    }
  </style>
</head>
<body>
  <div class="container">
    <header class="header">
      <h1>🛡️ Torque Studio - Reporte de Auditoría</h1>
      <p>Generado: ${new Date().toLocaleString('es-CL')} | ${this.bugs.length} bugs encontrados</p>
    </header>
    
    <!-- Metrics -->
    <div class="metrics-grid">
      <div class="metric-card">
        <div class="metric-value critical">${summary.bySeverity.critical}</div>
        <div class="metric-label">🚨 Críticos</div>
      </div>
      <div class="metric-card">
        <div class="metric-value high">${summary.bySeverity.high}</div>
        <div class="metric-label">🔴 Altos</div>
      </div>
      <div class="metric-card">
        <div class="metric-value medium">${summary.bySeverity.medium}</div>
        <div class="metric-label">🟡 Medios</div>
      </div>
      <div class="metric-card">
        <div class="metric-value low">${summary.bySeverity.low}</div>
        <div class="metric-label">🟢 Bajos</div>
      </div>
      <div class="metric-card">
        <div class="metric-value success">${this.metrics.passed}</div>
        <div class="metric-label">✅ Tests OK</div>
      </div>
      <div class="metric-card">
        <div class="metric-value info">${this.metrics.totalTests}</div>
        <div class="metric-label">🧪 Total Tests</div>
      </div>
    </div>
    
    <!-- Charts -->
    <div class="charts-grid">
      <div class="chart-card">
        <h3>Distribución por Severidad</h3>
        <canvas id="severityChart"></canvas>
      </div>
      <div class="chart-card">
        <h3>Bugs por Categoría</h3>
        <canvas id="categoryChart"></canvas>
      </div>
    </div>
    
    <!-- Export -->
    <div class="export-section">
      <button class="export-btn" onclick="exportJSON()">📥 Exportar JSON</button>
      <button class="export-btn" onclick="exportCSV()">📊 Exportar CSV</button>
      <button class="export-btn" onclick="window.print()">🖨️ Imprimir</button>
    </div>
    
    <!-- Filters -->
    <div class="filters">
      <button class="filter-btn active" onclick="filterBugs('all')">Todos (${this.bugs.length})</button>
      <button class="filter-btn" onclick="filterBugs('critical')">Críticos (${summary.bySeverity.critical})</button>
      <button class="filter-btn" onclick="filterBugs('high')">Altos (${summary.bySeverity.high})</button>
      <button class="filter-btn" onclick="filterBugs('medium')">Medios (${summary.bySeverity.medium})</button>
      <button class="filter-btn" onclick="filterBugs('low')">Bajos (${summary.bySeverity.low})</button>
    </div>
    
    <!-- Search -->
    <input type="text" class="search-box" placeholder="🔍 Buscar bugs..." onkeyup="searchBugs(this.value)">
    
    <!-- Bug List -->
    <div class="bug-list" id="bugList">
      ${this.generateBugListHTML()}
    </div>
  </div>
  
  <script>
    // Charts
    const severityData = {
      labels: ['Críticos', 'Altos', 'Medios', 'Bajos', 'Info'],
      datasets: [{
        data: [${summary.bySeverity.critical}, ${summary.bySeverity.high}, ${summary.bySeverity.medium}, ${summary.bySeverity.low}, ${summary.bySeverity.info}],
        backgroundColor: ['#dc2626', '#ea580c', '#ca8a04', '#16a34a', '#2563eb']
      }]
    };
    
    new Chart(document.getElementById('severityChart'), {
      type: 'doughnut',
      data: severityData,
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
    
    const categoryData = {
      labels: ${JSON.stringify(Object.keys(summary.byCategory))},
      datasets: [{
        data: ${JSON.stringify(Object.values(summary.byCategory))},
        backgroundColor: ['#4d8eff', '#8ab4f8', '#6b8cff', '#a78bfa', '#f472b6']
      }]
    };
    
    new Chart(document.getElementById('categoryChart'), {
      type: 'bar',
      data: categoryData,
      options: { 
        responsive: true,
        indexAxis: 'y',
        plugins: { legend: { display: false } }
      }
    });
    
    // Filtering
    const allBugs = ${JSON.stringify(this.bugs)};
    
    function filterBugs(severity) {
      document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
      event.target.classList.add('active');
      
      const filtered = severity === 'all' 
        ? allBugs 
        : allBugs.filter(b => b.severity === severity);
      
      renderBugs(filtered);
    }
    
    function searchBugs(query) {
      const filtered = allBugs.filter(b => 
        b.title.toLowerCase().includes(query.toLowerCase()) ||
        b.description.toLowerCase().includes(query.toLowerCase())
      );
      renderBugs(filtered);
    }
    
    function renderBugs(bugs) {
      const list = document.getElementById('bugList');
      list.innerHTML = bugs.map(bug => \`
        <div class="bug-item \${bug.severity}">
          <div class="bug-header">
            <div class="bug-title">[\${bug.severity.toUpperCase()}] \${bug.title}</div>
            <div class="bug-id">\${bug.id}</div>
          </div>
          <div class="bug-description">\${bug.description}</div>
          <div class="bug-meta">
            <span class="badge badge-\${bug.severity}">\${bug.severity}</span>
            <span class="badge" style="background: rgba(255,255,255,0.1);">\${bug.category}</span>
            <span style="color: #6b7280; font-size: 0.85rem;">\${bug.timestamp}</span>
          </div>
        </div>
      \`).join('');
    }
    
    // Export functions
    function exportJSON() {
      const data = ${JSON.stringify({ bugs: this.bugs, summary })};
      const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'audit-report-\${new Date().toISOString().split('T')[0]}.json';
      a.click();
    }
    
    function exportCSV() {
      const bugs = ${JSON.stringify(this.bugs)};
      const csv = [
        'ID,Severidad,Categoría,Título,Descripción,Fecha',
        ...bugs.map(b => \`"\${b.id}","\${b.severity}","\${b.category}","\${b.title}","\${b.description}","\${b.timestamp}"\`)
      ].join('\\n');
      
      const blob = new Blob([csv], { type: 'text/csv' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'audit-report-\${new Date().toISOString().split('T')[0]}.csv';
      a.click();
    }
  </script>
</body>
</html>`;
  }

  private generateBugListHTML(): string {
    if (this.bugs.length === 0) {
      return '<div style="text-align: center; padding: 40px; color: #16a34a;"><h2>🎉 Sin bugs encontrados</h2><p>Todos los tests pasaron correctamente</p></div>';
    }

    return this.bugs.map(bug => `
      <div class="bug-item ${bug.severity}">
        <div class="bug-header">
          <div class="bug-title">[${bug.severity.toUpperCase()}] ${bug.title}</div>
          <div class="bug-id">${bug.id}</div>
        </div>
        <div class="bug-description">${bug.description}</div>
        <div class="bug-meta">
          <span class="badge badge-${bug.severity}">${bug.severity}</span>
          <span class="badge" style="background: rgba(255,255,255,0.1);">${bug.category}</span>
          <span style="color: #6b7280; font-size: 0.85rem;">${bug.timestamp}</span>
        </div>
      </div>
    `).join('');
  }
}

export default AdvancedReporter;
