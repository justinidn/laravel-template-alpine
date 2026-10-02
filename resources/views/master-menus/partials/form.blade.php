<div class="modal fade"
    id="masterMenuModal"
    tabindex="-1"
    aria-labelledby="masterMenuModalLabel"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
    x-data="masterMenuModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <form @submit.prevent="submitForm">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="masterMenuModalLabel" x-text="isEdit ? 'Edit Master Menus' : 'Add Master Menus'"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <input type="hidden" x-model="form.id">

                    <div class="mb-3">
                        <x-input-field name="display_name" label="Display Name" placeholder="Enter display name..." required />
                    </div>

                    <div class="mb-4">
                        <x-input-field name="name" label="Route Name" placeholder="Enter name..." required />
                    </div>

                    <!-- Permissions Section (2 Kolom Grid) -->
                    <div class="pt-2">
                        <label class="form-label fw-bold text-dark mb-3 fs-7 text-uppercase">Menu Permissions</label>

                        <div class="row g-3">
                            <!-- View -->
                            <div class="col-6">
                                <label class="form-label fw-semibold mb-1">View Permission</label>
                                <x-toggle-switch name="perm_view" label="" model="form.permissions.view" labelExpression="form.permissions.view ? 'On' : 'Off'" wrapperClass="form-check form-switch d-flex align-items-center gap-2 ps-0" labelClass="form-check-label small text-muted" class="ms-0" />
                            </div>

                            <!-- Add -->
                            <div class="col-6">
                                <label class="form-label fw-semibold mb-1">Add Permission</label>
                                <x-toggle-switch name="perm_add" label="" model="form.permissions.add" labelExpression="form.permissions.add ? 'On' : 'Off'" wrapperClass="form-check form-switch d-flex align-items-center gap-2 ps-0" labelClass="form-check-label small text-muted" class="ms-0" />
                            </div>

                            <!-- Update -->
                            <div class="col-6">
                                <label class="form-label fw-semibold mb-1">Update Permission</label>
                                <x-toggle-switch name="perm_update" label="" model="form.permissions.update" labelExpression="form.permissions.update ? 'On' : 'Off'" wrapperClass="form-check form-switch d-flex align-items-center gap-2 ps-0" labelClass="form-check-label small text-muted" class="ms-0" />
                            </div>

                            <!-- Delete -->
                            <div class="col-6">
                                <label class="form-label fw-semibold mb-1">Delete Permission</label>
                                <x-toggle-switch name="perm_delete" label="" model="form.permissions.delete" labelExpression="form.permissions.delete ? 'On' : 'Off'" wrapperClass="form-check form-switch d-flex align-items-center gap-2 ps-0" labelClass="form-check-label small text-muted" class="ms-0" />
                            </div>

                            <!-- Export -->
                            <div class="col-6">
                                <label class="form-label fw-semibold mb-1">Export Permission</label>
                                <x-toggle-switch name="perm_export" label="" model="form.permissions.export" labelExpression="form.permissions.export ? 'On' : 'Off'" wrapperClass="form-check form-switch d-flex align-items-center gap-2 ps-0" labelClass="form-check-label small text-muted" class="ms-0" />
                            </div>
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
        Alpine.data('masterMenuModal', () => ({
            isEdit: false,
            errors: {},
            bsModal: null,
            form: {
                id: '',
                display_name: '',
                name: '',
                permissions: {
                    view: true,
                    add: false,
                    update: false,
                    delete: false,
                    export: false
                }
            },

            defaultForm() {
                return {
                    id: '',
                    display_name: '',
                    name: '',
                    permissions: {
                        view: true,
                        add: false,
                        update: false,
                        delete: false,
                        export: false
                    }
                };
            },

            init() {
                this.bsModal = new bootstrap.Modal(document.getElementById('masterMenuModal'), {
                    animation: false
                });

                window.addEventListener('open-master-menus-modal', (e) => {
                    this.errors = {};
                    const data = e.detail;

                    if (data && data.id) {
                        this.isEdit = true;
                        this.form = {
                            id: data.id ?? '',
                            display_name: data.display_name ?? '',
                            name: data.name ?? '',
                            permissions: {
                                view: Boolean(data.permissions?.view ?? true),
                                add: Boolean(data.permissions?.add ?? false),
                                update: Boolean(data.permissions?.update ?? false),
                                delete: Boolean(data.permissions?.delete ?? false),
                                export: Boolean(data.permissions?.export ?? false)
                            }
                        };
                    } else {
                        this.isEdit = false;
                        this.form = this.defaultForm();
                    }

                    this.bsModal.show();
                });
            },

            submitForm() {
                this.errors = {};

                axios.post("{{ route('master-menus.store') }}", this.form)
                    .then(() => {
                        this.bsModal.hide();
                        window.dispatchEvent(new CustomEvent('master-menus-table-reload'));
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