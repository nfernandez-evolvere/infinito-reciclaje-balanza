/* global React, Chart, Button, Card, Field, Icon, Pill,
   SERVICIOS, ZONAS, VEHICLE_TYPES,
   ZONE_BREAKDOWN, TYPE_BREAKDOWN, DAILY_EVOLUTION, fmtT, fmtPct */
const { useState, useEffect, useRef } = React;

function Reportes() {
  const [from, setFrom] = useState("2026-03-01");
  const [to, setTo] = useState("2026-03-12");
  const [zona, setZona] = useState("Todas");
  const [servicio, setServicio] = useState("Todos");
  const [tipo, setTipo] = useState("Todos");
  const [generated, setGenerated] = useState(false);

  return (
    <div className="page">
      <h1>Reportes</h1>
      <p className="lede">Generá reportes filtrados del período. Vista previa antes de exportar.</p>

      <Card title="Filtros">
        <div className="grid grid-4" style={{ gap: 12 }}>
          <Field label="Desde"><input type="date" className="input" value={from} onChange={(e) => setFrom(e.target.value)} /></Field>
          <Field label="Hasta"><input type="date" className="input" value={to}   onChange={(e) => setTo(e.target.value)} /></Field>
          <Field label="Zona">
            <select className="select" value={zona} onChange={(e) => setZona(e.target.value)}>
              <option>Todas</option>{ZONAS.map((z) => <option key={z}>{z}</option>)}
            </select>
          </Field>
          <Field label="Tipo de servicio">
            <select className="select" value={servicio} onChange={(e) => setServicio(e.target.value)}>
              <option>Todos</option>{SERVICIOS.map((s) => <option key={s}>{s}</option>)}
            </select>
          </Field>
          <Field label="Tipo de vehículo">
            <select className="select" value={tipo} onChange={(e) => setTipo(e.target.value)}>
              <option>Todos</option>{Object.keys(VEHICLE_TYPES).map((t) => <option key={t}>{t}</option>)}
            </select>
          </Field>
          <div style={{ alignSelf: "end", display: "flex", gap: 8 }}>
            <Button kind="primary" icon="play" onClick={() => setGenerated(true)}>Generar reporte</Button>
          </div>
        </div>
        <div style={{ display: "flex", gap: 6, marginTop: 12, flexWrap: "wrap" }}>
          <Pill kind="gray">{from} → {to}</Pill>
          {zona !== "Todas" && <Pill kind="gray">Zona: {zona}</Pill>}
          {servicio !== "Todos" && <Pill kind="gray">Servicio: {servicio}</Pill>}
          {tipo !== "Todos" && <Pill kind="gray">Tipo: {tipo}</Pill>}
        </div>
      </Card>

      {generated && (
        <div className="section" style={{ marginTop: 24 }}>
          <div className="section-head">
            <div>
              <h2>Reporte de actividad</h2>
              <p className="muted body-sm" style={{ marginTop: 4 }}>Período {from} al {to} · Generado el {new Date().toLocaleDateString("es-AR")}</p>
            </div>
            <div className="toolbar">
              <Button kind="secondary" icon="file-down">Descargar PDF</Button>
              <Button kind="secondary" icon="file-spreadsheet">Descargar Excel</Button>
            </div>
          </div>

          <ReportPreview />
        </div>
      )}

      {!generated && (
        <div className="card" style={{ marginTop: 16, textAlign: "center", padding: 48, color: "var(--ink-500)" }}>
          <div style={{ display: "inline-flex", flexDirection: "column", alignItems: "center", gap: 12 }}>
            <Icon name="bar-chart-3" size={32} />
            <div>Aplicá los filtros y generá el reporte para ver la vista previa.</div>
          </div>
        </div>
      )}
    </div>
  );
}

function ReportPreview() {
  const canvasRef = useRef(null);
  useEffect(() => {
    if (!canvasRef.current || !window.Chart) return;
    const ctx = canvasRef.current.getContext("2d");
    const chart = new Chart(ctx, {
      type: "bar",
      data: {
        labels: DAILY_EVOLUTION.map((d) => d.day),
        datasets: [{
          data: DAILY_EVOLUTION.map((d) => d.t),
          backgroundColor: "#2E7D32",
          borderRadius: 3,
          barThickness: 28,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false }, ticks: { font: { family: "Inter" }, color: "#757575" } },
          y: { grid: { color: "#E0E0E0" }, ticks: { font: { family: "Inter" }, color: "#757575", callback: (v) => v + " t" } },
        },
      },
    });
    return () => chart.destroy();
  }, []);

  return (
    <>
      <div className="grid grid-4" style={{ marginBottom: 16 }}>
        <div className="kpi sm"><div className="label">Pesajes</div><div className="value">892</div></div>
        <div className="kpi sm"><div className="label">Toneladas</div><div className="value">3.240<span className="unit">t</span></div></div>
        <div className="kpi sm"><div className="label">Días op.</div><div className="value">22</div></div>
        <div className="kpi sm"><div className="label">Promedio / día</div><div className="value">147,3<span className="unit">t</span></div></div>
      </div>

      <Card title="Evolución diaria" subtitle="toneladas netas">
        <div className="chart-wrap"><canvas ref={canvasRef} /></div>
      </Card>

      <div className="grid grid-2" style={{ marginTop: 16 }}>
        <Card title="Por zona">
          <table className="table">
            <thead><tr><th>Zona</th><th className="num">Pesajes</th><th className="num">Toneladas</th><th className="num">Generación (kg/ha)</th></tr></thead>
            <tbody>
              {ZONE_BREAKDOWN.map((z) => (
                <tr key={z.zona}><td><Pill kind="gray">{z.zona}</Pill></td><td className="num">{z.pesajes}</td><td className="num">{fmtT(z.t)}</td><td className="num">{z.kgHa}</td></tr>
              ))}
            </tbody>
          </table>
        </Card>
        <Card title="Por tipo de vehículo">
          <table className="table">
            <thead><tr><th>Tipo</th><th className="num">Viajes</th><th className="num">Toneladas</th><th className="num">%</th></tr></thead>
            <tbody>
              {TYPE_BREAKDOWN.map((t) => (
                <tr key={t.tipo}><td><b>{t.tipo}</b></td><td className="num">{t.viajes}</td><td className="num">{fmtT(t.t)}</td><td className="num">{fmtPct(t.pct)}</td></tr>
              ))}
            </tbody>
          </table>
        </Card>
      </div>

      <Card title="Densidad de generación" subtitle="Toneladas netas por hectárea servida, según zona" style={{ marginTop: 16 }}>
        <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
          {ZONE_BREAKDOWN.map((z) => {
            const max = Math.max(...ZONE_BREAKDOWN.map((x) => x.kgHa));
            return (
              <div key={z.zona} style={{ display: "grid", gridTemplateColumns: "100px 1fr 60px", alignItems: "center", gap: 12, fontSize: 13 }}>
                <span style={{ color: "var(--ink-700)" }}>{z.zona}</span>
                <div style={{ height: 12, background: "var(--bg)", borderRadius: 999, overflow: "hidden" }}>
                  <div style={{ width: `${(z.kgHa / max) * 100}%`, height: "100%", background: "var(--green-700)" }} />
                </div>
                <span className="num" style={{ textAlign: "right", color: "var(--ink-900)", fontWeight: 600 }}>{z.kgHa.toLocaleString("es-AR", { minimumFractionDigits: 1, maximumFractionDigits: 1 })}</span>
              </div>
            );
          })}
        </div>
      </Card>
    </>
  );
}

window.Reportes = Reportes;
