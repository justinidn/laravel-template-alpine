<x-app-layout>
    <div class="container-fluid px-2 py-2">
        <div class="mb-3">
            {{ Breadcrumbs::render('profile.edit') }}
        </div>

        <header class="mb-4">
            <h1 class="h4 fw-semibold text-dark mb-1">{{ __('Profile') }}</h1>
            <p class="text-secondary mb-0">{{ __('Manage your account details and security settings.') }}</p>
        </header>

        <div class="row g-3 align-items-stretch">
            <div class="col-12 col-xl-6">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="col-12 col-xl-6">
                @include('profile.partials.update-password-form')
            </div>

            <div class="col-12">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>