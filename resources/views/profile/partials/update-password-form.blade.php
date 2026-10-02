<section class="h-100 p-4 bg-white border-custom-light rounded-3 shadow-sm">
    <header class="mb-4">
        <h2 class="h6 fw-semibold text-dark mb-1">
            {{ __('Update Password') }}
        </h2>

        <p class="small text-secondary mb-0">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="d-flex flex-column gap-3">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" class="form-label fw-semibold mb-1" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="form-control border-custom-light" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="small text-danger mt-1" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" class="form-label fw-semibold mb-1" />
            <x-text-input id="update_password_password" name="password" type="password" class="form-control border-custom-light" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="small text-danger mt-1" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" class="form-label fw-semibold mb-1" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control border-custom-light" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="small text-danger mt-1" />
        </div>

        <div class="d-flex align-items-center gap-3">
            <x-primary-button class="btn btn-primary btn-sm px-3">{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'password-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="small text-success mb-0">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>