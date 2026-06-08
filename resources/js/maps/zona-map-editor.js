import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import '@geoman-io/leaflet-geoman-free';
import '@geoman-io/leaflet-geoman-free/dist/leaflet-geoman.css';
import './choropleth.css';

// Iconos por defecto de Leaflet (Vite no resuelve las rutas automáticamente).
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

// Centro por defecto del mapa cuando la zona todavía no tiene geometría: Corrientes, AR.
const DEFAULT_CENTER = [-27.4698, -58.8306];
const DEFAULT_ZOOM = 12;

const GUIDE_STYLE = {
    color: '#3b82f6',
    weight: 2,
    opacity: 0.75,
    fillColor: '#3b82f6',
    fillOpacity: 0.12,
    dashArray: '5 4',
    interactive: false,
};

/**
 * Editor de polígono de una zona sobre Leaflet + Geoman.
 * La instancia vive fuera del estado reactivo de Alpine (Leaflet no tolera ser proxeado).
 *
 * @param {{ onChange: (payload: { geojson: string, lat: number|string, lng: number|string }) => void }} opts
 */
export function createZonaMapEditor({ onChange }) {
    let map = null;
    let guideLayer = null;
    let drawn = null;

    function mount(el) {
        if (map) return;

        map = L.map(el, { zoomControl: true }).setView(DEFAULT_CENTER, DEFAULT_ZOOM);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }).addTo(map);

        // guideLayer va antes que drawn para que quede debajo del polígono editable.
        guideLayer = L.featureGroup().addTo(map);
        drawn = L.featureGroup().addTo(map);

        // Toda capa dibujada se agrega directo a nuestro featureGroup.
        map.pm.setGlobalOptions({ layerGroup: drawn });
        map.pm.setLang('es');
        map.pm.addControls({
            position: 'topleft',
            drawPolygon: true,
            editMode: true,
            dragMode: true,
            removalMode: true,
            rotateMode: false,
            cutPolygon: false,
            drawMarker: false,
            drawCircle: false,
            drawCircleMarker: false,
            drawPolyline: false,
            drawRectangle: false,
            drawText: false,
        });

        map.on('pm:create', ({ layer }) => {
            keepOnly(layer);
            bindLayer(layer);
            sync();
        });
    }

    // Una sola zona = un solo polígono. Al dibujar uno nuevo, descartamos el anterior.
    function keepOnly(layer) {
        drawn.eachLayer((l) => {
            if (l !== layer) drawn.removeLayer(l);
        });
    }

    function bindLayer(layer) {
        layer.on('pm:edit', sync);
        layer.on('pm:dragend', sync);
        layer.on('pm:remove', () => {
            drawn.removeLayer(layer);
            sync();
        });
    }

    function sync() {
        if (!drawn.getLayers().length) {
            onChange({ geojson: '', lat: '', lng: '' });
            return;
        }
        const center = drawn.getBounds().getCenter();
        onChange({
            geojson: JSON.stringify(drawn.toGeoJSON()),
            lat: Number(center.lat.toFixed(7)),
            lng: Number(center.lng.toFixed(7)),
        });
    }

    function setGeometria(geojson) {
        if (!drawn) return;
        drawn.clearLayers();

        let data = geojson;
        if (typeof geojson === 'string') {
            try {
                data = JSON.parse(geojson);
            } catch {
                data = null;
            }
        }

        if (data) {
            L.geoJSON(data).eachLayer((layer) => {
                drawn.addLayer(layer);
                bindLayer(layer);
            });
        }

        if (drawn.getLayers().length) {
            map.fitBounds(drawn.getBounds(), { padding: [24, 24], maxZoom: 16 });
        } else {
            map.setView(DEFAULT_CENTER, DEFAULT_ZOOM);
        }
    }

    function updateGuide(zonas, currentId) {
        if (!guideLayer) return;
        guideLayer.clearLayers();
        if (!zonas || !zonas.length) return;

        zonas.forEach(({ id, nombre, geojson }) => {
            if (id === currentId) return;
            if (!geojson) return;

            let data;
            try {
                data = typeof geojson === 'string' ? JSON.parse(geojson) : geojson;
            } catch {
                return;
            }
            if (!data) return;

            const geoLayer = L.geoJSON(data, {
                style: GUIDE_STYLE,
                interactive: false,
                onEachFeature: (_, l) => {
                    l.options.pmIgnore = true;
                    l.bindTooltip(nombre, {
                        permanent: true,
                        direction: 'center',
                        className: 'zona-guide-label',
                    });
                },
            });
            geoLayer.options.pmIgnore = true;
            guideLayer.addLayer(geoLayer);
        });
    }

    /**
     * Se llama cada vez que el modal se abre: el contenedor estaba oculto (display:none),
     * así que hay que recalcular el tamaño antes de pintar la geometría.
     */
    function show(geojson, guideZonas, currentId) {
        if (!map) return;
        setTimeout(() => {
            map.invalidateSize();
            updateGuide(guideZonas || [], currentId ?? null);
            setGeometria(geojson);
        }, 60);
    }

    function destroy() {
        if (map) {
            map.remove();
            map = null;
            guideLayer = null;
            drawn = null;
        }
    }

    return { mount, show, destroy };
}
