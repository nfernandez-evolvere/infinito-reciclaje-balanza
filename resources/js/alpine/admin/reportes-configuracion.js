export default (initial = {}) => ({
    servicios: initial.servicios ?? [{ titulo: '', descripcion: '' }],
    aiEnabled: initial.aiEnabled ?? false,

    addServicio() {
        this.servicios.push({ titulo: '', descripcion: '' });
    },

    removeServicio(i) {
        this.servicios.splice(i, 1);
    },
});
