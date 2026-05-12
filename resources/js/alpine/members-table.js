export default (Alpine) => {
    Alpine.data('memberTable', (initialIds) => ({
        search:       '',
        roleFilter:   '',
        statusFilter: '',
        selected:     [],
        visibleIds:   initialIds,
        visibleCount: initialIds.length,

        get allSelected() {
            return this.visibleIds.length > 0 && this.visibleIds.every(id => this.selected.includes(id))
        },
        get someSelected() {
            return this.visibleIds.some(id => this.selected.includes(id)) && !this.allSelected
        },

        filterRows() {
            const q = this.search.toLowerCase()
            const vis = []
            document.querySelectorAll('.member-row').forEach(row => {
                const match =
                    (!q || row.dataset.name.includes(q) || row.dataset.email.includes(q)) &&
                    (!this.roleFilter   || row.dataset.role   === this.roleFilter) &&
                    (!this.statusFilter || row.dataset.status === this.statusFilter)
                row.style.display = match ? '' : 'none'
                if (match) vis.push(row.dataset.id)
            })
            this.visibleIds   = vis
            this.visibleCount = vis.length
        },

        toggleSelect(id) {
            const i = this.selected.indexOf(id)
            i >= 0 ? this.selected.splice(i, 1) : this.selected.push(id)
        },
        toggleAll()      { this.selected = this.allSelected ? [] : [...this.visibleIds] },
        clearSelection() { this.selected = [] },
    }))
}
