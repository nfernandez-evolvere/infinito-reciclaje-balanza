import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import './choropleth.css';

const DEFAULT_CENTER = [-27.4698, -58.8306]; // Corrientes, AR
const DEFAULT_ZOOM = 12;
const BORDER = '#475569'; // slate-600 — borde de los polígonos

/**
 * Mapa de coropletas read-only: pinta el polígono de cada zona según una métrica.
 * Vive fuera del estado reactivo de Alpine.
 *
 * @param {HTMLElement} el  contenedor del mapa
 */
export function createChoroplethMap(el) {
    const map = L.map(el, { zoomControl: true }).setView(DEFAULT_CENTER, DEFAULT_ZOOM);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap',
    }).addTo(map);

    const group = L.featureGroup().addTo(map);
    let entries = []; // { zona, layer }

    function setZonas(zonas) {
        group.clearLayers();
        entries = [];

        zonas.forEach((zona) => {
            if (!zona.geojson) return;
            const layer = L.geoJSON(zona.geojson, {
                style: { color: BORDER, weight: 2, fillOpacity: 0.7 },
            });
            layer.addTo(group);

            // Etiqueta permanente con el nombre, centrada dentro del área.
            L.tooltip({
                permanent: true,
                direction: 'center',
                className: 'zona-label',
                interactive: false,
            })
                .setLatLng(layer.getBounds().getCenter())
                .setContent(zona.nombre)
                .addTo(group);

            entries.push({ zona, layer });
        });

        if (entries.length) {
            setTimeout(() => {
                map.invalidateSize();
                map.fitBounds(group.getBounds(), { padding: [24, 24], maxZoom: 15 });
            }, 60);
        }
    }

    function recolor(colorFn, tooltipFn) {
        entries.forEach(({ zona, layer }) => {
            layer.setStyle({ fillColor: colorFn(zona), fillOpacity: 0.7, color: BORDER, weight: 2 });
            layer.bindTooltip(tooltipFn(zona), { sticky: true, direction: 'top' });
        });
    }

    // Re-mide el contenedor y reencuadra. Necesario cuando el mapa estuvo dentro
    // de un tab oculto (display:none → 0px): al revelarlo hay que invalidar el
    // tamaño cacheado por Leaflet y volver a ajustar los límites.
    function resize() {
        map.invalidateSize();
        if (entries.length) {
            map.fitBounds(group.getBounds(), { padding: [24, 24], maxZoom: 15 });
        }
    }

    return { setZonas, recolor, resize };
}
