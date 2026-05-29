export default ({ name = '', fetchUrl = null, value = '' } = {}) => ({
    name,
    fetchUrl,
    tags:        value ? value.split(',').map(e => e.trim()).filter(Boolean) : [],
    query:       '',
    suggestions: [],
    open:        false,
    loading:     false,
    _timer:      null,

    init() {
        // Permite que el padre reinicialice los tags vía evento
        window.addEventListener('tags-input-set', (e) => {
            if (e.detail?.name === this.name) {
                this.tags  = e.detail.tags ?? [];
                this.query = '';
                this.open  = false;
            }
        });
    },

    get serialized() {
        return this.tags.join(', ');
    },

    search() {
        clearTimeout(this._timer);
        const delay = this.query.length === 0 ? 0 : 250;
        this._timer = setTimeout(async () => {
            if (!this.fetchUrl) return;
            this.loading = true;
            try {
                const url = `${this.fetchUrl}?q=${encodeURIComponent(this.query)}`;
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                this.suggestions = res.ok ? await res.json() : [];
                this.open = this.suggestions.length > 0 || this.isValid(this.query);
            } finally {
                this.loading = false;
            }
        }, delay);
    },

    add(value) {
        value = value.trim().toLowerCase();
        if (value && !this.tags.includes(value)) {
            this.tags.push(value);
        }
        this.query       = '';
        this.suggestions = [];
        this.open        = false;
        this.$nextTick(() => this.$refs.tagsInput?.focus());
    },

    remove(i) {
        this.tags.splice(i, 1);
    },

    keydown(e) {
        if (['Enter', ','].includes(e.key) && this.query.trim()) {
            e.preventDefault();
            this.add(this.query);
        }
        if (e.key === 'Backspace' && !this.query && this.tags.length) {
            this.tags.pop();
        }
        if (e.key === 'Escape') {
            this.open = false;
        }
    },

    isValid(v) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test((v || '').trim());
    },

    alreadySuggested(v) {
        return this.suggestions.some(s => s.email === (v || '').trim().toLowerCase());
    },
});
