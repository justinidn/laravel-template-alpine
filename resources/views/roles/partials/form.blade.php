<div class="modal fade"
    id="roleModal"
    tabindex="-1"
    aria-labelledby="roleModalLabel"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
    x-data="roleModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <form @submit.prevent="submitForm">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="roleModalLabel" x-text="isEdit ? 'Edit Role' : 'Add Role'"></h5>
                    <button type="button" class="btn-close  " data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <input type="hidden" x-model="form.id">

                    <div class="mb-4">
                        <x-input-field name="name" label="Role Name" placeholder="Enter role name..." required />
                    </div>

                    <!-- Permissions Section Dinamis Per Menu -->
                    <div class="pt-2">
                        <div class="d-flex flex-column gap-4" style="max-height: 400px; overflow-y: auto;">
                            <template x-for="menu in masterMenus" :key="menu.id">
                                <div x-show="menu.available_actions && menu.available_actions.length > 0">
                                    <!-- Header Menu & Select All Switch -->
                                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                        <label class="form-label fw-bold text-dark fs-7 text-uppercase mb-0" x-text="menu.display_name + ' PERMISSIONS'"></label>
                                        <div class="form-check form-switch d-flex align-items-center gap-2 ps-0 m-0">
                                            <input class="form-check-input ms-0"
                                                type="checkbox"
                                                :id="'perm_all_' + menu.id"
                                                :checked="isAllSelected(menu)"
                                                @change="toggleSelectAll(menu, $event.target.checked)">
                                            <label class="form-check-label fw-semibold text-primary small"
                                                :for="'perm_all_' + menu.id">Select All</label>
                                        </div>
                                    </div>

                                    <!-- Permissions Grid 2 Kolom (Sesuai Desain Master Menu) -->
                                    <div class="row g-3">
                                        <template x-for="item in menu.available_actions" :key="item.full_name">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold mb-1 text-capitalize"
                                                    :for="'perm_' + menu.id + '_' + item.action"
                                                    x-text="item.action + ' Permission'"></label>
                                                <div class="form-check form-switch d-flex align-items-center gap-2 ps-0">
                                                    <input class="form-check-input ms-0"
                                                        type="checkbox"
                                                        :id="'perm_' + menu.id + '_' + item.action"
                                                        :checked="Boolean(form.permissions[item.full_name])"
                                                        @change="togglePermission(menu, item, $event.target.checked)">
                                                    <label class="form-check-label small text-muted"
                                                        :for="'perm_' + menu.id + '_' + item.action"
                                                        x-text="form.permissions[item.full_name] ? 'On' : 'Off'"></label>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <div class="row w-100 g-2 m-0">
                        <div class="col-6 ps-0">
                            <x-save-button class="w-100" />
                        </div>
                        <div class="col-6 pe-0">
                            <button type="button" class="btn btn-outline-secondary w-100 btn-sm" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('roleModal', () => ({
            isEdit: false,
            errors: {},
            bsModal: null,
            masterMenus: [],
            selectAllByMenu: {},
            form: {
                id: '',
                name: '',
                permissions: {}
            },

            defaultForm() {
                return {
                    id: '',
                    name: '',
                    permissions: {}
                };
            },

            init() {
                this.bsModal = new bootstrap.Modal(document.getElementById('roleModal'), {
                    animation: false
                });

                window.addEventListener('open-roles-modal', async (e) => {
                    this.errors = {};
                    const data = e.detail;

                    await this.fetchMasterMenus();

                    if (data && data.id) {
                        this.isEdit = true;

                        if (data.permissions && Array.isArray(data.permissions)) {
                            this.populateForm(data);
                        } else {
                            axios.get(`/roles/${data.id}`)
                                .then(res => {
                                    this.populateForm(res.data.data);
                                })
                                .catch(() => {
                                    this.populateForm(data);
                                });
                        }
                    } else {
                        this.isEdit = false;
                        this.form = this.defaultForm();
                        this.syncSelectAllStates();
                    }

                    this.bsModal.show();
                });
            },

            populateForm(data) {
                let assignedPermissions = {};
                if (data.permissions && Array.isArray(data.permissions)) {
                    data.permissions.forEach(perm => {
                        assignedPermissions[perm.name] = true;
                    });
                }

                this.form = {
                    id: data.id ?? '',
                    name: data.name ?? '',
                    permissions: assignedPermissions
                };

                this.syncSelectAllStates();
            },

            async fetchMasterMenus() {
                try {
                    const res = await axios.get("{{ route('roles.master-menus-list') }}");
                    this.masterMenus = res.data.data || res.data;
                } catch (error) {
                    console.error('Error fetching master menus:', error);
                }
            },

            toggleSelectAll(menu, checked) {
                this.selectAllByMenu[menu.id] = checked;

                if (menu.available_actions) {
                    menu.available_actions.forEach(item => {
                        this.form.permissions[item.full_name] = checked;
                    });
                }
            },

            togglePermission(menu, item, checked) {
                this.form.permissions[item.full_name] = checked;
                this.selectAllByMenu[menu.id] = this.areAllPermissionsSelected(menu);
            },

            syncSelectAllStates() {
                this.selectAllByMenu = {};
                this.masterMenus.forEach(menu => {
                    this.selectAllByMenu[menu.id] = this.areAllPermissionsSelected(menu);
                });
            },

            areAllPermissionsSelected(menu) {
                return Boolean(menu.available_actions?.length) &&
                    menu.available_actions.every(item => Boolean(this.form.permissions[item.full_name]));
            },

            isAllSelected(menu) {
                return Boolean(this.selectAllByMenu[menu.id]);
            },

            submitForm() {
                this.errors = {};

                axios.post("{{ route('roles.store') }}", this.form)
                    .then(() => {
                        this.bsModal.hide();
                        window.dispatchEvent(new CustomEvent('roles-table-reload'));
                        alert('Data successfully saved!');
                    })
                    .catch(err => {
                        if (err.response?.status === 422) {
                            this.errors = err.response.data.errors;
                        } else {
                            alert('System error occurred. Please try again.');
                        }
                    });
            }
        }));
    });
</script>
@endpush