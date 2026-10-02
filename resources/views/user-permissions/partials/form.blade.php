<div class="modal fade"
    id="userPermissionModal"
    tabindex="-1"
    aria-labelledby="userPermissionModalLabel"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
    x-data="userPermissionModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <form @submit.prevent="submitForm">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="userPermissionModalLabel" x-text="isEdit ? 'Edit User Permissions' : 'Add User Permissions'"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <input type="hidden" x-model="form.id">

                    <div class="mb-4">
                        <label for="user_id" class="form-label fw-semibold">User <span class="text-danger">*</span></label>
                        <select id="user_id"
                            class="form-select rounded-0 border-0 border-bottom border-secondary bg-transparent shadow-none px-0"
                            :class="{'border-danger': errors.user_id}"
                            x-model="form.user_id"
                            :disabled="isEdit"
                            required>
                            <option value="">Select user...</option>
                            <template x-for="user in users" :key="user.id">
                                <option :value="user.id" x-text="`${user.name} (${user.email})`"></option>
                            </template>
                        </select>
                        <template x-if="errors.user_id">
                            <div class="text-danger fs-7 mt-1" x-text="errors.user_id[0]"></div>
                        </template>
                    </div>

                    <div class="pt-2">
                        <div class="d-flex flex-column gap-4" style="max-height: 300px; overflow-y: auto;">
                            <template x-for="menu in menus" :key="menu.id">
                                <div x-show="menu.available_actions && menu.available_actions.length > 0">
                                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                        <label class="form-label fw-bold text-dark fs-7 text-uppercase mb-0" x-text="menu.display_name + ' PERMISSIONS'"></label>
                                        <div class="form-check form-switch d-flex align-items-center gap-2 ps-0 m-0">
                                            <input class="form-check-input ms-0"
                                                type="checkbox"
                                                :id="'user_perm_all_' + menu.id"
                                                :checked="isAllSelected(menu)"
                                                @change="toggleSelectAll(menu, $event.target.checked)">
                                            <label class="form-check-label fw-semibold text-primary small"
                                                :for="'user_perm_all_' + menu.id">Select All</label>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <template x-for="item in menu.available_actions" :key="item.full_name">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold mb-1 text-capitalize"
                                                    :for="'user_perm_' + menu.id + '_' + item.action"
                                                    x-text="item.action + ' Permission'"></label>
                                                <div class="form-check form-switch d-flex align-items-center gap-2 ps-0">
                                                    <input class="form-check-input ms-0"
                                                        type="checkbox"
                                                        :id="'user_perm_' + menu.id + '_' + item.action"
                                                        :checked="Boolean(form.permissions[item.full_name])"
                                                        @change="togglePermission(menu, item, $event.target.checked)">
                                                    <label class="form-check-label small text-muted"
                                                        :for="'user_perm_' + menu.id + '_' + item.action"
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
        Alpine.data('userPermissionModal', () => ({
            isEdit: false,
            errors: {},
            bsModal: null,
            users: [],
            menus: [],
            selectAllByMenu: {},
            form: {
                id: '',
                user_id: '',
                permissions: {},
            },

            defaultForm() {
                return {
                    id: '',
                    user_id: '',
                    permissions: {},
                };
            },

            init() {
                this.bsModal = new bootstrap.Modal(document.getElementById('userPermissionModal'), {
                    animation: false
                });

                window.addEventListener('open-user-permissions-modal', async (e) => {
                    this.errors = {};
                    const data = e.detail;
                    await this.fetchAssignmentOptions();

                    if (data && data.id) {
                        this.isEdit = true;
                        this.form = this.populateForm(data);
                    } else {
                        this.isEdit = false;
                        this.form = this.defaultForm();
                    }

                    this.syncSelectAllStates();
                    this.bsModal.show();
                });
            },

            async fetchAssignmentOptions() {
                try {
                    const res = await axios.get("{{ route('user-permissions.assignment-options') }}");
                    this.users = res.data.data.users || [];
                    this.menus = res.data.data.menus || [];
                } catch (error) {
                    console.error('Error fetching user role options:', error);
                }
            },

            populateForm(data) {
                return {
                    id: data.id,
                    user_id: data.id,
                    permissions: (data.permissions || []).reduce((permissions, permission) => {
                        permissions[permission.name] = true;
                        return permissions;
                    }, {}),
                };
            },

            toggleSelectAll(menu, checked) {
                this.selectAllByMenu[menu.id] = checked;

                menu.available_actions.forEach(permission => {
                    this.form.permissions[permission.full_name] = checked;
                });
            },

            togglePermission(menu, permission, checked) {
                this.form.permissions[permission.full_name] = checked;
                this.selectAllByMenu[menu.id] = this.areAllPermissionsSelected(menu);
            },

            syncSelectAllStates() {
                this.selectAllByMenu = {};
                this.menus.forEach(menu => {
                    this.selectAllByMenu[menu.id] = this.areAllPermissionsSelected(menu);
                });
            },

            areAllPermissionsSelected(menu) {
                return Boolean(menu.available_actions?.length) &&
                    menu.available_actions.every(permission => Boolean(this.form.permissions[permission.full_name]));
            },

            isAllSelected(menu) {
                return Boolean(this.selectAllByMenu[menu.id]);
            },

            submitForm() {
                this.errors = {};
                const form = {
                    ...this.form,
                    permissions: Object.keys(this.form.permissions).filter(permission => this.form.permissions[permission]),
                };

                axios.post("{{ route('user-permissions.store') }}", form)
                    .then(() => {
                        this.bsModal.hide();
                        window.dispatchEvent(new CustomEvent('user-permissions-table-reload'));
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