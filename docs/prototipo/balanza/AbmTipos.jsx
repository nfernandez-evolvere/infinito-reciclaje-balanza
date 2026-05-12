/* global React, Button, Card, Field, Icon, Pill,
   VEHICLE_TYPES, fmtN */
const { useState } = React;

function AbmTipos() {
  const [rows, setRows] = useState(() =>
    Object.entries(VEHICLE_TYPES).map(([nombre, r], i) => ({ id: i + 1, nombre, ...r, estado: "Activo" }))
  );

  const updateRow = (id, patch) => setRows((rs) => rs.map((r) => r.id === id ? { ...r, ...patch } : r));

  return (
    <div className="page">
      <div style={{ display: "flex", alignItems: "flex-end", justifyContent: "space-between", marginBottom: 4 }}>
        <div>
          <h1>Tipos de vehículo</h1>
          <p className="lede">Rangos de peso bruto habituales por tipo. Se usan en la validación al pesar.</p>
        </div>
        <Button icon="plus" disabled>Agregar tipo</Button>
      </div>

      <Card compact>
        <div style={{ overflowX: "auto" }}>
        <table className="table">
          <thead>
            <tr>
              <th>Tipo</th>
              <th className="num">Rango mínimo</th>
              <th className="num">Rango máximo</th>
              <th>Estado</th>
              <th style={{ textAlign: "right" }}>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {rows.map((r) => (
              <tr key={r.id}>
                <td><b>{r.nombre}</b></td>
                <td className="num">{fmtN(r.rangeMin)} kg</td>
                <td className="num">{fmtN(r.rangeMax)} kg</td>
                <td><Pill kind="green" dot>Activo</Pill></td>
                <td style={{ textAlign: "right" }}>
                  <div className="actions">
                    <button className="btn btn-ghost btn-sm" title="Editar rangos"><Icon name="pencil" size={14} /></button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
        </div>
      </Card>

      <div className="banner info" style={{ marginTop: 16 }}>
        <div className="ic" style={{ color: "var(--blue-700)" }}><Icon name="info" size={20} /></div>
        <div>
          <div className="title">Estos rangos son informativos, no bloquean el guardado.</div>
          <div className="body">Si un pesaje queda fuera del rango, se marca como anomalía en el dashboard pero el operador puede igual registrarlo.</div>
        </div>
      </div>
    </div>
  );
}

window.AbmTipos = AbmTipos;
