/* global React, Chart, Card, KpiCard, Banner, Pill, Icon,
   ALERTS, DAILY_EVOLUTION, ZONE_BREAKDOWN, TYPE_BREAKDOWN, fmtN, fmtT, fmtPct, fmtKg */
const { useEffect, useRef } = React;

function Dashboard({ pesajes = [] }) {
  const canvasRef = useRef(null);
  const enPredio = pesajes.filter((p) => p.estado === "En predio");

  useEffect(() => {
    if (!canvasRef.current || !window.Chart) return;
    const ctx = canvasRef.current.getContext("2d");
    const data = DAILY_EVOLUTION.map((d) => d.t);
    const labels = DAILY_EVOLUTION.map((d) => d.day);
    const avg = data.reduce((a, b) => a + b, 0) / data.length;

    const chart = new Chart(ctx, {
      type: "bar",
      data: {
        labels,
        datasets: [
          {
            label: "Toneladas",
            data,
            backgroundColor: labels.map((l) => l === "Hoy" ? "#2E7D32" : "#A5D6A7"),
            borderRadius: 4,
            barThickness: 32,
          },
          {
            type: "line",
            label: "Promedio",
            data: data.map(() => avg),
            borderColor: "#757575",
            borderDash: [4, 4],
            borderWidth: 1.5,
            pointRadius: 0,
            tension: 0,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: "#212121",
            padding: 10,
            titleFont: { family: "Inter", weight: "600" },
            bodyFont: { family: "Inter" },
            callbacks: {
              label: (ctx) => ctx.dataset.label === "Promedio"
                ? `Promedio · ${avg.toFixed(1)} t`
                : `${ctx.parsed.y.toLocaleString("es-AR", { minimumFractionDigits: 1, maximumFractionDigits: 1 })} t`,
            },
          },
        },
        scales: {
          x: { grid: { display: false }, ticks: { font: { family: "Inter", size: 12 }, color: "#757575" } },
          y: {
            grid: { color: "#E0E0E0", drawBorder: false },
            ticks: {
              font: { family: "Inter", size: 11 },
              color: "#757575",
              callback: (v) => v + " t",
            },
            beginAtZero: true,
          },
        },
      },
    });
    return () => chart.destroy();
  }, []);

  return (
    <div className="page">
      <h1>Dashboard</h1>
      <p className="lede">Operación del predio · jueves 12 de marzo, turno tarde.</p>

      {/* Alerts */}
      {ALERTS.length > 0 && (
        <div className="section" style={{ gap: 8 }}>
          {ALERTS.map((a, i) => (
            <Banner key={i} kind={a.kind} title={a.title} body={a.body}
              actions={<button className="btn btn-ghost btn-sm">Revisar</button>} />
          ))}
        </div>
      )}

      {/* En predio */}
      {enPredio.length > 0 && (
        <div className="section">
          <div className="section-head">
            <h3>Camiones en el predio</h3>
            <span className="muted body-sm">{enPredio.length} camión{enPredio.length === 1 ? "" : "es"} sin egreso registrado</span>
          </div>
          <Card compact>
            <div style={{ overflowX: "auto" }}>
              <table className="table">
                <thead>
                  <tr>
                    <th>Patente</th>
                    <th>Tipo</th>
                    <th>Servicio</th>
                    <th>Zona</th>
                    <th className="num">Entrada</th>
                    <th className="num">Neto registrado</th>
                    <th>Operador</th>
                  </tr>
                </thead>
                <tbody>
                  {enPredio.map((p) => (
                    <tr key={p.id}>
                      <td><b>{p.patente}</b></td>
                      <td>{p.tipo}</td>
                      <td>{p.servicio}</td>
                      <td><Pill kind="gray">{p.zona}</Pill></td>
                      <td className="num">{p.horaEntrada}</td>
                      <td className="num">{fmtKg(p.neto)}</td>
                      <td className="muted">{p.operador}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </Card>
        </div>
      )}

      {/* KPIs del día */}
      <div className="section">
        <div className="section-head">
          <h3>KPIs del día</h3>
          <span className="muted body-sm">Actualizado hace 1 min</span>
        </div>
        <div className="grid grid-4">
          <KpiCard label="Pesajes"        value="42"    meta="vs. 38 promedio (7 días)" />
          <KpiCard label="Toneladas"      value="142,5" unit="t" meta="vs. 138,2 t promedio" />
          <KpiCard label="Promedio / viaje" value="3,39" unit="t" meta="dentro del rango habitual" />
          <KpiCard label="Horas operativas" value="6" unit="h" meta="turno tarde — 14:00 a 20:00" />
        </div>
      </div>

      {/* KPIs del mes */}
      <div className="section">
        <div className="section-head"><h3>KPIs del mes</h3></div>
        <div className="grid grid-3">
          <KpiCard sm label="Pesajes acumulados" value="892" />
          <KpiCard sm label="Toneladas acumuladas" value="3.240" unit="t" />
          <KpiCard sm label="Días operativos" value="22" />
        </div>
      </div>

      {/* Chart */}
      <div className="section">
        <Card title="Evolución diaria" subtitle="Últimos 7 días · toneladas netas">
          <div className="chart-wrap"><canvas ref={canvasRef} /></div>
        </Card>
      </div>

      {/* Breakdowns */}
      <div className="grid grid-2">
        <Card title="Por zona" subtitle="Toneladas netas y generación por hectárea servida">
          <table className="table">
            <thead>
              <tr>
                <th>Zona</th>
                <th className="num">Pesajes</th>
                <th className="num">Toneladas</th>
                <th className="num" title="Toneladas netas dispuestas por hectárea de superficie servida por la zona.">Generación (kg/ha)</th>
              </tr>
            </thead>
            <tbody>
              {ZONE_BREAKDOWN.map((z) => (
                <tr key={z.zona}>
                  <td><Pill kind="gray">{z.zona}</Pill></td>
                  <td className="num">{z.pesajes}</td>
                  <td className="num">{fmtT(z.t)}</td>
                  <td className="num">{z.kgHa.toLocaleString("es-AR", { minimumFractionDigits: 1, maximumFractionDigits: 1 })}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </Card>

        <Card title="Por tipo de vehículo" subtitle="Viajes registrados y participación">
          <table className="table">
            <thead>
              <tr>
                <th>Tipo</th>
                <th className="num">Viajes</th>
                <th className="num">Toneladas</th>
                <th className="num">%</th>
              </tr>
            </thead>
            <tbody>
              {TYPE_BREAKDOWN.map((t) => (
                <tr key={t.tipo}>
                  <td><b>{t.tipo}</b></td>
                  <td className="num">{t.viajes}</td>
                  <td className="num">{fmtT(t.t)}</td>
                  <td className="num">
                    <div style={{ display: "inline-flex", alignItems: "center", gap: 8 }}>
                      <div style={{ width: 80, height: 6, background: "var(--bg)", borderRadius: 999, overflow: "hidden" }}>
                        <div style={{ width: `${t.pct}%`, height: "100%", background: "var(--green-700)" }} />
                      </div>
                      <span>{fmtPct(t.pct)}</span>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </Card>
      </div>
    </div>
  );
}

window.Dashboard = Dashboard;
