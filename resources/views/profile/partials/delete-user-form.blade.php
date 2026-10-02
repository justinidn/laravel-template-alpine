<section class="p-4 bg-white border border-danger-subtle rounded-3 shadow-sm">
    <header class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <div>
            <h2 class="h6 fw-semibold text-dark mb-1">
                {{ __('Delete Account') }}
            </h2>

            <p class="small text-secondary mb-0">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
            </p>
        </div>

        <x-danger-button
            type="button"
            class="btn btn-outline-danger btn-sm flex-shrink-0"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">{{ __('Delete Account') }}</x-danger-button>
    </header>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" maxWidth="md" focusable>
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')

            <div class="modal-header">
                <h2 class="modal-title h6 fw-semibold" id="confirm-user-deletion-label">
                    {{ __('Are you sure you want to delete your account?') }}
                </h2>
                <button type="button" class="btn-close" aria-label="Close" x-on:click="$dispatch('close')"></button>
            </div>

            <div class="modal-body">
                <p class="small text-secondary">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </p>

                <x-input-label for="password" :value="__('Password')" class="form-label fw-semibold mb-1" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="form-control border-custom-light"
                    autocomplete="current-password" />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="small text-danger mt-1" />
            </div>

            <div class="modal-footer">
                <x-secondary-button class="btn btn-light border btn-sm" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="btn btn-danger btn-sm">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>