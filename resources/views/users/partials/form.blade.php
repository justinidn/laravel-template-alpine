<div class="modal fade"
    id="userModal"
    tabindex="-1"
    aria-labelledby="userModalLabel"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
    x-data="userModal"
    data-departments="{{ json_encode($departments) }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <form @submit.prevent="submitForm">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="userModalLabel" x-text="isEdit ? 'Edit Users' : 'Add Users'"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <input type="hidden" x-model="form.id">

                    <!-- ISI SESUAI FIELD FORM ANDA -->
                    <div class="mb-3">
                        <x-input-field name="name" label="Name" required />
                    </div>
                    <template x-if="!isEdit">
                        <div class="mb-4">
                            <x-input-field name="password" label="Password" type="password" autocomplete="new-password" required />
                        </div>
                    </template>
                    <div class="mb-4">
                        <x-select-field name="department_id" label="Department" options="departments" required />
                    </div>
                    <div class="mb-4">
                        <x-input-field name="email" label="Email" type="email" required />
                    </div>
                    <div class="mb-4">
                        <x-input-field type="number" name="nrk" label="NRK" maxlength="7" required />
                    </div>
                    <x-toggle-switch name="is_active" label="Active Status" showIf="isEdit" />
                    <x-toggle-switch name="system_login" label="Allow Login" />
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
        Alpine.data('userModal', () => ({
            isEdit: false,
            errors: {},
            bsModal: null,
            departments: [],
            form: {
                id: '',
                name: '',
                password: '',
                department_id: '',
                is_active: true,
                system_login: true
            },

            defaultForm() {
                return {
                    id: '',
                    name: '',
                    password: '',
                    department_id: '',
                    is_active: true,
                    system_login: true
                };
            },

            init() {
                this.departments = JSON.parse(this.$el.dataset.departments || '{}');
                this.bsModal = new bootstrap.Modal(document.getElementById('userModal'), {
                    animation: false
                });

                window.addEventListener('open-users-modal', (e) => {
                    this.errors = {};
                    const data = e.detail;

                    if (data && data.id) {
                        this.isEdit = true;
                        this.form = {
                            ...data,
                            is_active: Boolean(data.is_active),
                            system_login: Boolean(data.system_login)
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

                axios.post("{{ route('users.store') }}", this.form)
                    .then(() => {
                        this.bsModal.hide();
                        window.dispatchEvent(new CustomEvent('users-table-reload'));
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