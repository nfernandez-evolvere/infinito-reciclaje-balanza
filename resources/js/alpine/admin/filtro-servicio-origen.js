// Vincula el select de «Origen» (zonas) con el de «Servicio» en los filtros de
// pesajes: las zonas dependen del servicio (cada zona pertenece a un tipo de
// servicio), así que al elegir un servicio solo se ofrecen sus orígenes y, si la
// zona elegida ya no pertenece al servicio seleccionado, se limpia.
export default function filtroServicioOrigen({ servicio = '', zona = '', zonas = [] }) {
    return {
        servicio: servicio ? String(servicio) : '',
        zona: zona ? String(zona) : '',
        zonas: zonas.map(z => ({ id: String(z.id), tipoServicioId: String(z.tipo_servicio_id) })),

        get zonasFiltradas() {
            return this.servicio
                ? this.zonas.filter(z => z.tipoServicioId === this.servicio)
                : this.zonas;
        },

        init() {
            this.$watch('servicio', () => {
                if (this.zona && !this.zonasFiltradas.some(z => z.id === this.zona)) {
                    this.zona = '';
                }
            });
        },
    };
}
