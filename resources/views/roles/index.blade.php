<x-app-layout>
    <div class="container-fluid px-2 py-2" x-data="rolesPage">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h4 fw-semibold text-dark mb-0">Roles</h1>
            </div>

            <div class="text-md-end">
                <x-create-button event="open-roles-modal" />
            </div>
        </div>

        <!-- Main Card Section -->
        <div class="bg-white border-custom-light rounded-4 shadow-sm overflow-hidden">
            <!-- Filter & Search Bar Header -->
            <div class="p-3 border-bottom border-custom-light">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <!-- Search Input -->
                    <div class="input-group flex-grow-1">
                        <span class="input-group-text bg-white border-end-0 text-muted-custom rounded-start-3">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="search"
                            id="search"
                            class="form-control border-start-0 ps-0 outline-royalblue rounded-end-3"
                            placeholder="Search Roles..."
                            x-model.debounce.500ms="search">
                    </div>


                </div>
            </div>

            <!-- Datatable Card Component -->
            <div>
                <x-datatable-card
                    tableId="roles-table"
                    ajaxUrl="{{ route('roles.index') }}">
                    <x-slot:header>
                        <th class="py-3 ps-3 text-secondary text-center" width="5%">NO</th>

                        <th class="py-3 text-secondary">Name</th>

                        @canany(['roles.update', 'roles.delete'])
                        <th class="py-3 pe-3 text-end text-secondary" width="5%">ACTIONS</th>
                        @endcanany
                    </x-slot:header>
                </x-datatable-card>
            </div>
        </div>

        @include('roles.partials.form')
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('rolesPage', () => ({
                search: '',
                statusFilter: '',
                tempStatus: '',

                init() {
                    this.$watch('search', () => {
                        this.reloadTable();
                    });

                    window.editRoles = id => this.editItem(id);
                    window.deleteRoles = id => this.deleteItem(id);
                },

                reloadTable() {
                    const tableComponent = this.$root.querySelector('[x-data^="datatableComponent"]');
                    const tableData = tableComponent && Alpine.$data(tableComponent);

                    if (tableData?.table) {
                        const url = new URL("{{ route('roles.index') }}", window.location.origin);

                        if (this.statusFilter !== '') {
                            url.searchParams.set('status', this.statusFilter);
                        }

                        tableData.table.ajax.url(url.toString());
                    }

                    window.dispatchEvent(new CustomEvent('roles-table-reload'));
                },

                applyFilters() {
                    this.statusFilter = this.tempStatus;
                    this.reloadTable();
                },

                resetFilters() {
                    this.tempStatus = '';
                    this.statusFilter = '';
                    this.reloadTable();
                    this.closeDropdown();
                },

                closeDropdown() {
                    const dropdownEl = this.$refs.filterDropdown.querySelector('[data-bs-toggle="dropdown"]');
                    if (dropdownEl) {
                        const bsDropdown = bootstrap.Dropdown.getInstance(dropdownEl) || new bootstrap.Dropdown(dropdownEl);
                        if (bsDropdown) bsDropdown.hide();
                    }
                },

                getExtraApiParams() {
                    return {
                        search: this.search,
                        status: this.statusFilter
                    };
                },

                getTableColumns() {
                    return [{
                            data: null,
                            name: 'no',
                            orderable: false,
                            searchable: false,
                            className: 'ps-3 text-dark fw-semibold text-nowrap text-center',
                            render: (data, type, row, meta) => meta.row + this.displayStart + 1
                        },

                        {
                            data: 'name',
                            name: 'name',
                            orderable: true,
                            render: data => `<span class="fw-semibold text-dark">${data ?? '-'}</span>`
                        },

                        {
                            data: 'id',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'text-end pe-3 text-nowrap',
                            render: data => `
                                @canany(['roles.update', 'roles.delete'])
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end p-2">
                                        @can('roles.update')
                                        <li>
                                            <a class="dropdown-item rounded" href="javascript:void(0)" onclick="window.editRoles(${data})">
                                                Edit
                                            </a>
                                        </li>
                                        @endcan
                                        @can('roles.delete')
                                        <li>
                                            <a class="dropdown-item rounded text-danger" href="javascript:void(0)" onclick="window.deleteRoles(${data})">
                                                Delete
                                            </a>
                                        </li>
                                        @endcan
                                    </ul>
                                </div>
                                @endcanany
                            `
                        }
                    ];
                },

                editItem(id) {
                    axios.get(`/roles/${id}/edit`)
                        .then(res => {
                            window.dispatchEvent(new CustomEvent('open-roles-modal', {
                                detail: res.data.data
                            }));
                        })
                        .catch(() => alert('Failed to fetch data.'));
                },

                deleteItem(id) {
                    if (!confirm('Are you sure you want to delete this record?')) return;

                    axios.delete(`/roles/${id}`, {
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(() => {
                            this.reloadTable();
                            alert('Data successfully deleted!');
                        })
                        .catch(() => alert('Failed to delete data.'));
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>