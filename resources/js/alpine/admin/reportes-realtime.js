/**
 * Suscripción a las notificaciones de reportes en tiempo real (Echo + Reverb).
 *
 * Escucha el canal privado del usuario y, por cada cambio de estado
 * (en_revision | enviado | fallido), reparte el evento a las tres piezas de UI:
 *   - toast (sonner) en la sesión conectada,
 *   - campana del topbar (evento 'reporte-notificacion'),
 *   - refresco en vivo de la tabla del historial (evento 'reporte-estado').
 *
 * Defensivo: sin usuario logueado o sin Echo (página pública, WS deshabilitado)
 * no hace nada. La notificación persistente ya quedó en la campana del lado
 * servidor, así que perder el push nunca pierde la novedad.
 */
export default function initReportesRealtime() {
    const userId = window.__USER_ID;

    if (!userId || !window.Echo) {
        return;
    }

    window.Echo.private(`user.${userId}.reportes`).listen('.reporte.estado', (e) => {
        if (window.Alpine && e.toast) {
            window.Alpine.store('toast')?.add(e.toast);
        }

        if (e.alerta) {
            window.dispatchEvent(new CustomEvent('reporte-notificacion', { detail: e.alerta }));
        }

        // Actualiza en vivo el contador de pendientes (badge de la pestaña + banner).
        if (window.Alpine && typeof e.pendientes_revision !== 'undefined') {
            const pendientes = window.Alpine.store('reportesPendientes');
            if (pendientes) {
                pendientes.count = e.pendientes_revision;
            }
        }

        window.dispatchEvent(new CustomEvent('reporte-estado', { detail: e }));
    });
}
