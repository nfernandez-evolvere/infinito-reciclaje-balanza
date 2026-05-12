/* global React, lucide */
const { useEffect, useRef } = React;

// Lucide icon helper — renders <i data-lucide=...> and triggers replacement.
function Icon({ name, size = 16, color, style }) {
  const ref = useRef(null);
  useEffect(() => {
    if (window.lucide && ref.current) {
      ref.current.innerHTML = `<i data-lucide="${name}"></i>`;
      window.lucide.createIcons({ attrs: { width: size, height: size }, nameAttr: "data-lucide" });
    }
  }, [name, size]);
  return <span ref={ref} className="icn" style={{ width: size, height: size, color, lineHeight: 0, ...style }} />;
}

function Button({ kind = "primary", size, block, type = "button", icon, iconRight, children, ...rest }) {
  const cls = ["btn", `btn-${kind}`];
  if (size === "sm") cls.push("btn-sm");
  if (block) cls.push("btn-block");
  return (
    <button type={type} className={cls.join(" ")} {...rest}>
      {icon && <Icon name={icon} size={16} />}
      {children}
      {iconRight && <Icon name={iconRight} size={16} />}
    </button>
  );
}

function Field({ label, hint, hintKind, children, style }) {
  return (
    <div className="field" style={style}>
      {label && <span className="label">{label}</span>}
      {children}
      {hint && <span className={"help" + (hintKind ? " " + hintKind : "")}>{hint}</span>}
    </div>
  );
}

function Card({ title, subtitle, action, children, compact, style }) {
  return (
    <div className={"card" + (compact ? " compact" : "")} style={style}>
      {(title || action) && (
        <div className="card-title">
          <span>{title}{subtitle && <span className="subtitle" style={{ marginLeft: 8 }}>{subtitle}</span>}</span>
          {action}
        </div>
      )}
      {children}
    </div>
  );
}

function Pill({ kind = "gray", children, dot }) {
  return (
    <span className={"pill " + kind}>
      {dot && <span className="dot" />}
      {children}
    </span>
  );
}

function Badge({ kind, label, value }) {
  return (
    <span className={"badge" + (kind ? " " + kind : "")}>
      {label}: <b>{value}</b>
    </span>
  );
}

function KpiCard({ label, value, unit, meta, sm }) {
  return (
    <div className={"kpi" + (sm ? " sm" : "")}>
      <div className="label">{label}</div>
      <div className="value">{value}{unit && <span className="unit">{unit}</span>}</div>
      {meta && <div className="meta">{meta}</div>}
    </div>
  );
}

function Banner({ kind = "warn", title, body, actions }) {
  const iconName = kind === "warn" ? "alert-triangle"
                 : kind === "danger" ? "alert-octagon"
                 : kind === "info" ? "info"
                 : "check-circle-2";
  const color = kind === "warn" ? "var(--orange-700)"
              : kind === "danger" ? "var(--red-700)"
              : kind === "info" ? "var(--blue-700)"
              : "var(--green-700)";
  return (
    <div className={"banner " + kind}>
      <div className="ic" style={{ color }}><Icon name={iconName} size={20} /></div>
      <div>
        <div className="title">{title}</div>
        {body && <div className="body">{body}</div>}
      </div>
      {actions && <div className="actions">{actions}</div>}
    </div>
  );
}

function Modal({ title, onClose, children, footer, maxWidth = 560 }) {
  return (
    <div className="scrim" onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}>
      <div className="modal" style={{ maxWidth }}>
        <h3>{title}</h3>
        {children}
        {footer && <div className="modal-footer">{footer}</div>}
      </div>
    </div>
  );
}

function SuccessOverlay({ patente, neto, onDone }) {
  useEffect(() => {
    const t = setTimeout(onDone, 1100);
    return () => clearTimeout(t);
  }, []);
  return (
    <div className="success-overlay" aria-live="polite">
      <div className="success-card">
        <div className="ring"><Icon name="check" size={36} /></div>
        <div className="title">Pesaje guardado</div>
        <div className="meta">{patente} · {window.fmtKg(neto)} netos</div>
      </div>
    </div>
  );
}

function Toast({ kind = "info", title, body, onDismiss, duration = 2400 }) {
  useEffect(() => {
    if (!duration) return;
    const t = setTimeout(onDismiss, duration);
    return () => clearTimeout(t);
  }, [duration, onDismiss]);
  const color = kind === "warn" ? "var(--orange-700)"
              : kind === "danger" ? "var(--red-700)"
              : kind === "ok" ? "var(--green-700)"
              : "var(--blue-700)";
  const iconName = kind === "warn" ? "alert-triangle"
                 : kind === "danger" ? "alert-octagon"
                 : kind === "ok" ? "check-circle-2"
                 : "info";
  return (
    <div style={{ position: "fixed", left: "50%", bottom: 80, transform: "translateX(-50%)", zIndex: 200, background: "var(--ink-900)", color: "white", borderRadius: 8, padding: "12px 16px", boxShadow: "var(--shadow-pop)", display: "flex", alignItems: "center", gap: 10, minWidth: 320, animation: "pop 180ms var(--ease)" }}>
      <span style={{ color, display: "grid", placeItems: "center" }}><Icon name={iconName} size={18} /></span>
      <div style={{ fontSize: 13 }}>
        <div style={{ fontWeight: 600 }}>{title}</div>
        {body && <div style={{ opacity: 0.8, marginTop: 2 }}>{body}</div>}
      </div>
    </div>
  );
}

Object.assign(window, { Icon, Button, Field, Card, Pill, Badge, KpiCard, Banner, Modal, SuccessOverlay, Toast });
