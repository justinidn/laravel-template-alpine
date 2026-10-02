<section class="h-100 p-4 bg-white border-custom-light rounded-3 shadow-sm">
    <header class="mb-4">
        <h2 class="h6 fw-semibold text-dark mb-1">
            {{ __('Profile Information') }}
        </h2>

        <p class="small text-secondary mb-0">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="d-flex flex-column gap-3">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" class="form-label fw-semibold mb-1" />
            <x-text-input id="name" name="name" type="text" class="form-control border-custom-light" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="small text-danger mt-1" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" class="form-label fw-semibold mb-1" />
            <x-text-input id="email" name="email" type="email" class="form-control border-custom-light" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="small text-danger mt-1" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="alert alert-warning d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 py-2 px-3 mt-3 mb-0">
                <span class="small"><i class="bi bi-exclamation-circle me-2"></i>{{ __('Your email address is unverified.') }}</span>
                <button form="send-verification" class="btn btn-sm btn-outline-secondary flex-shrink-0">
                    {{ __('Click here to re-send the verification email.') }}
                </button>

                @if (session('status') === 'verification-link-sent')
                <p class="small text-success mb-0">
                    {{ __('A new verification link has been sent to your email address.') }}
                </p>
                @endif
            </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <x-primary-button class="btn btn-primary btn-sm px-3">{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
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