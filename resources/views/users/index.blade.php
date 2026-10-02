<x-app-layout>
    <div class="container-fluid px-2 py-2" x-data="usersPage">
        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h4 fw-semibold text-dark mb-0">Users</h1>
            </div>

            <div class="text-md-end">
                <x-create-button event="open-users-modal" />
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
                            placeholder="Search Users..."
                            x-model.debounce.500ms="search">
                    </div>

                    <!-- Filter Dropdown & Export Button -->
                    <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-md-2">
                        <div class="dropdown" x-ref="filterDropdown">
                            <button type="button"
                                class="btn btn-light border text-secondary rounded-3 d-flex align-items-center gap-2 shadow-sm dropdown-toggle"
                                data-bs-toggle="dropdown"
                                data-bs-auto-close="outside"
                                aria-expanded="false">
                                <i class="bi bi-funnel"></i>
                                <span>Filters</span>
                            </button>

                            <div class="dropdown-menu dropdown-menu-end p-3 shadow-lg border-custom-light rounded-3" style="width: 240px;">
                                <h6 class="fw-bold text-dark mb-2 fs-7 text-uppercase">Filter Status</h6>

                                <div class="mb-3">
                                    <select class="form-select form-select-sm outline-royalblue" x-model="tempStatus">
                                        <option value="">All Status</option>
                                        <option value="1">Active Only</option>
                                        <option value="0">Inactive Only</option>
                                    </select>
                                </div>

                                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-custom-light">
                                    <button type="button"
                                        class="btn btn-link btn-sm text-decoration-none text-muted p-0 fs-7"
                                        @click="resetFilters()">
                                        Reset
                                    </button>
                                    <button type="button"
                                        class="btn btn-royalblue btn-sm px-3 py-1 fs-7"
                                        @click="applyFilters()">
                                        Apply
                                    </button>
                                </div>
                            </div>
                        </div>
                        @can('users.export')
                        <button type="button"
                            class="btn btn-light border text-secondary rounded-3 d-flex align-items-center gap-2 shadow-sm"
                            @click="$dispatch('export-data')">
                            <i class="bi bi-box-arrow-up"></i>
                            <span>Export</span>
                        </button>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Datatable Card Component -->
            <div>
                <x-datatable-card
                    tableId="users-table"
                    ajaxUrl="{{ route('users.index') }}">
                    <x-slot:header>
                        <th class="py-3 ps-3 text-secondary text-center" width="5%">NO</th>

                        <th class="py-3 text-secondary">Nrk</th>
                        <th class="py-3 text-secondary">Name</th>
                        <th class="py-3 text-secondary">Email</th>
                        <th class="py-3 text-secondary">Department</th>

                        <th class="py-3 text-secondary">Is Active</th>
                        @canany(['users.update', 'users.delete'])
                        <th class="py-3 pe-3 text-end text-secondary" width="5%">ACTIONS</th>
                        @endcanany
                    </x-slot:header>
                </x-datatable-card>
            </div>
        </div>

        @include('users.partials.form')
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('usersPage', () => ({
                search: '',
                statusFilter: '',
                tempStatus: '',

                init() {
                    this.$watch('search', () => {
                        this.reloadTable();
                    });

                    window.editUsers = id => this.editItem(id);
                    window.deleteUsers = id => this.deleteItem(id);
                },

                reloadTable() {
                    const tableComponent = this.$root.querySelector('[x-data^="datatableComponent"]');
                    const tableData = tableComponent && Alpine.$data(tableComponent);

                    if (tableData?.table) {
                        const url = new URL("{{ route('users.index') }}", window.location.origin);

                        if (this.statusFilter !== '') {
                            url.searchParams.set('status', this.statusFilter);
                        }

                        tableData.table.ajax.url(url.toString());
                    }

                    window.dispatchEvent(new CustomEvent('users-table-reload'));
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
                            data: 'nrk',
                            name: 'nrk',
                            orderable: true,
                            render: data => `<span class="fw-semibold text-dark">${data ?? '-'}</span>`
                        },
                        {
                            data: 'name',
                            name: 'name',
                            orderable: true,
                            render: data => `<span class="fw-semibold text-dark">${data ?? '-'}</span>`
                        },
                        {
                            data: 'email',
                            name: 'email',
                            orderable: true,
                            render: data => `<span class="fw-semibold text-dark">${data ?? '-'}</span>`
                        },
                        {
                            data: 'department.department_alias',
                            name: 'department_id',
                            orderable: true,
                            render: data => `<span class="fw-semibold text-dark">${data ?? '-'}</span>`
                        },


                        {
                            data: 'is_active',
                            name: 'is_active',
                            orderable: true,
                            render: data => data ? `Active` : `Inactive`
                        },

                        {
                            data: 'id',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'text-end pe-3 text-nowrap',
                            render: data => `
                                @canany(['users.update', 'users.delete'])
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end p-2">
                                        @can('users.update')
                                        <li>
                                            <a class="dropdown-item rounded" href="javascript:void(0)" onclick="window.editUsers(${data})">
                                                Edit
                                            </a>
                                        </li>
                                        @endcan
                                        @can('users.delete')
                                        <li>
                                            <a class="dropdown-item rounded text-danger" href="javascript:void(0)" onclick="window.deleteUsers(${data})">
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
                    axios.get(`users/${id}/edit`)
                        .then(res => {
                            window.dispatchEvent(new CustomEvent('open-users-modal', {
                                detail: res.data.data
                            }));
                        })
                        .catch(() => alert('Failed to fetch data.'));
                },

                deleteItem(id) {
                    if (!confirm('Are you sure you want to delete this record?')) return;

                    axios.delete(`users/${id}`, {
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