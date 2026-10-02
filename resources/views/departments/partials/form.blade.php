<div class="modal fade"
    id="departmentsModal"
    tabindex="-1"
    aria-labelledby="departmentsModalLabel"
    aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false"
    x-data="departmentsModal">
    <div class="modal-dialog modal-dialog-centered ">
        <div class="modal-content shadow-lg border-0">
            <form @submit.prevent="submitForm">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="departmentsModalLabel" x-text="isEdit ? 'Edit Department' : 'Add Department'"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <input type="hidden" x-model="form.id">
                    <div class="mb-3">
                        <x-input-field name="department_alias" label="Department Alias" />
                    </div>
                    <div class="mb-3">
                        <x-input-field name="department_name" label="Department Name" required />
                    </div>
                    <x-toggle-switch name="is_active" label="Active Status" showIf="isEdit" />
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
        Alpine.data('departmentsModal', () => ({
            isEdit: false,
            errors: {},
            bsModal: null,
            form: {
                id: '',
                department_alias: '',
                department_name: '',
                is_active: true
            },

            defaultForm() {
                return {
                    id: '',
                    department_alias: '',
                    department_name: '',
                    is_active: true
                };
            },

            init() {
                this.bsModal = new bootstrap.Modal(document.getElementById('departmentsModal'), {
                    animation: false
                });

                window.addEventListener('open-departments-modal', (e) => {
                    this.errors = {};
                    const data = e.detail;

                    if (data && data.id) {
                        this.isEdit = true;
                        this.form = {
                            ...data,
                            is_active: Boolean(data.is_active)
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

                axios.post("{{ route('departments.store') }}", this.form)
                    .then(() => {
                        this.bsModal.hide();
                        window.dispatchEvent(new CustomEvent('departments-table-reload'));
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