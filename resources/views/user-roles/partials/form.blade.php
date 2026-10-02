<div class="modal fade"
    id="userRoleModal"
    tabindex="-1"
    aria-labelledby="userRoleModalLabel"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
    x-data="userRoleModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <form @submit.prevent="submitForm">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold" id="userRoleModalLabel">Edit User Roles</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label for="user_id" class="form-label fw-semibold">User <span class="text-danger">*</span></label>
                        <select id="user_id"
                            class="form-select rounded-0 border-0 border-bottom border-secondary bg-transparent shadow-none px-0"
                            :class="{'border-danger': errors.user_id}"
                            x-model="form.user_id"
                            disabled
                            required>
                            <option value="">Select user...</option>
                            <template x-for="user in users" :key="user.id">
                                <option :value="String(user.id)" x-text="`${user.name} (${user.email})`"></option>
                            </template>
                        </select>
                        <template x-if="errors.user_id">
                            <div class="text-danger fs-7 mt-1" x-text="errors.user_id[0]"></div>
                        </template>
                    </div>

                    <div>
                        <label class="form-label fw-semibold">Roles</label>
                        <div class="d-flex flex-column gap-2" style="max-height: 300px; overflow-y: auto;">
                            <template x-for="role in roles" :key="role.id">
                                <label class="form-check d-flex align-items-center gap-2 mb-0">
                                    <input class="form-check-input mt-0"
                                        type="checkbox"
                                        :value="String(role.id)"
                                        x-model="form.roles">
                                    <span class="form-check-label" x-text="role.name"></span>
                                </label>
                            </template>
                            <template x-if="roles.length === 0">
                                <p class="text-muted mb-0">No roles available.</p>
                            </template>
                        </div>
                        <template x-if="errors.roles">
                            <div class="text-danger fs-7 mt-1" x-text="errors.roles[0]"></div>
                        </template>
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
        Alpine.data('userRoleModal', () => ({
            isEdit: false,
            errors: {},
            bsModal: null,
            users: [],
            roles: [],
            form: {
                user_id: '',
                roles: [],
            },

            defaultForm() {
                return {
                    user_id: '',
                    roles: [],
                };
            },

            init() {
                this.bsModal = new bootstrap.Modal(document.getElementById('userRoleModal'), {
                    animation: false
                });

                window.addEventListener('open-user-roles-modal', async (e) => {
                    this.errors = {};
                    const data = e.detail;
                    await this.fetchAssignmentOptions();

                    this.form = {
                        user_id: String(data.id),
                        roles: (data.roles || []).map(role => String(role.id)),
                    };

                    this.bsModal.show();
                });
            },

            async fetchAssignmentOptions() {
                try {
                    const res = await axios.get("{{ route('user-roles.assignment-options') }}");
                    this.users = res.data.data.users || [];
                    this.roles = res.data.data.roles || [];
                } catch (error) {
                    console.error('Error fetching user role options:', error);
                }
            },

            submitForm() {
                this.errors = {};

                axios.post("{{ route('user-roles.store') }}", this.form)
                    .then(() => {
                        this.bsModal.hide();
                        window.dispatchEvent(new CustomEvent('user-roles-table-reload'));
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