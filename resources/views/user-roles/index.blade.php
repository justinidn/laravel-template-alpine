<x-app-layout>
    <div class="container-fluid px-2 py-2" x-data="userRolesPage">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h4 fw-semibold text-dark mb-0">User Roles</h1>
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
                            placeholder="Search User Roles..."
                            x-model.debounce.500ms="search">
                    </div>

                </div>
            </div>

            <!-- Datatable Card Component -->
            <div>
                <x-datatable-card
                    tableId="user-roles-table"
                    ajaxUrl="{{ route('user-roles.index') }}">
                    <x-slot:header>
                        <th class="py-3 ps-3 text-secondary text-center" width="5%">NO</th>

                        <th class="py-3 text-secondary">User</th>
                        <th class="py-3 text-secondary">Roles</th>

                        @can('user-roles.update')
                        <th class="py-3 pe-3 text-end text-secondary" width="5%">ACTIONS</th>
                        @endcan
                    </x-slot:header>
                </x-datatable-card>
            </div>
        </div>

        @include('user-roles.partials.form')
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('userRolesPage', () => ({
                search: '',

                init() {
                    this.$watch('search', () => {
                        this.reloadTable();
                    });

                    window.editUserRoles = id => this.editItem(id);
                },

                reloadTable() {
                    const tableComponent = this.$root.querySelector('[x-data^="datatableComponent"]');
                    const tableData = tableComponent && Alpine.$data(tableComponent);

                    if (tableData?.table) {
                        const url = new URL("{{ route('user-roles.index') }}", window.location.origin);
                        tableData.table.ajax.url(url.toString());
                    }

                    window.dispatchEvent(new CustomEvent('user-roles-table-reload'));
                },

                getExtraApiParams() {
                    return {
                        search: this.search
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
                            render: (data, type, row) => `<span class="fw-semibold text-dark">${this.escapeHtml(data)}<small class="d-block text-muted">${this.escapeHtml(row.email)}</small></span>`
                        },

                        {
                            data: 'roles',
                            name: 'roles',
                            orderable: false,
                            searchable: false,
                            render: roles => (roles || []).map(role => this.escapeHtml(role.name)).join(', ') || '-'
                        },

                        {
                            data: 'id',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'text-end pe-3 text-nowrap',
                            render: data => `
                                @can('user-roles.update')
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end p-2">
                                        <li>
                                            <a class="dropdown-item rounded" href="javascript:void(0)" onclick="window.editUserRoles(${data})">
                                                Edit
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                @endcan
                            `
                        }
                    ];
                },

                escapeHtml(value) {
                    const element = document.createElement('span');
                    element.textContent = value ?? '';
                    return element.innerHTML;
                },

                editItem(id) {
                    const url = "{{ route('user-roles.edit', '__ID__') }}".replace('__ID__', id);

                    axios.get(url)
                        .then(res => {
                            window.dispatchEvent(new CustomEvent('open-user-roles-modal', {
                                detail: res.data.data
                            }));
                        })
                        .catch(() => alert('Failed to fetch data.'));
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>