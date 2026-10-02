document.addEventListener('alpine:init', () => {
    Alpine.data('datatableComponent', (ajaxUrl, tableId) => ({
        table: null,
        displayStart: 0,

        init() {
            this.$nextTick(() => {
                const parentEl = this.$el.closest('[x-data]');
                const parentData = (parentEl && window.Alpine && typeof Alpine.$data === 'function') 
                    ? Alpine.$data(parentEl) 
                    : {};

                // Cek apakah komponen Alpine memiliki fungsi getTableColumns
                let cols = [];
                if (typeof this.getTableColumns === 'function') {
                    cols = this.getTableColumns();
                }

                this.table = new DataTable(`#${tableId}`, {
                    processing: false,
                    serverSide: true,
                    responsive: false,
                    searching: false,
                    lengthChange: false,
                    pageLength: 10,
                    ajax: {
                        url: ajaxUrl,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        data: data => {
                            this.displayStart = Number(data.start) || 0;
                            data.search = parentData.search ?? '';
                            data.inactive = parentData.inactiveOnly ? 1 : 0;
                        }
                    },
                    columns: cols
                });

                window.addEventListener(`${tableId}-reload`, () => {
                    this.table.ajax.reload(null, false);
                });
            });
        }
    }));
});