<section class="p-4 bg-white border-custom-light rounded-3 shadow-sm"
    x-data="twoFactorSettings"
    data-enabled="{{ $user->hasEnabledTwoFactorAuthentication() ? '1' : '0' }}"
    data-pending="{{ $user->two_factor_secret && ! $user->two_factor_confirmed_at ? '1' : '0' }}">
    <header class="mb-4">
        <h2 class="h6 fw-semibold text-dark mb-1">Two-Factor Authentication</h2>
        <p class="small text-secondary mb-0">Add an authenticator app as an optional sign-in step.</p>
    </header>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
        <div>
            <span class="badge"
                :class="enabled ? 'text-bg-success' : 'text-bg-secondary'"
                x-text="enabled ? 'Enabled' : (pending ? 'Setup pending' : 'Disabled')"></span>
            <p class="small text-secondary mb-0 mt-2" x-show="!enabled && !pending">
                Two-factor authentication is optional and can be turned off at any time.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <template x-if="!enabled && !pending">
                <button type="button" class="btn btn-primary btn-sm" @click="askForPassword('enable')">
                    <i class="bi bi-shield-lock me-1"></i> Enable 2FA
                </button>
            </template>

            <template x-if="pending">
                <button type="button" class="btn btn-primary btn-sm" @click="askForPassword('resume')">
                    Continue setup
                </button>
            </template>

            <template x-if="enabled">
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="askForPassword('recovery')">
                        View recovery codes
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" @click="askForPassword('disable')">
                        Disable 2FA
                    </button>
                </div>
            </template>
        </div>
    </div>

    <div class="border-top pt-3" x-show="pending && qrCodeSvg">
        <div class="row g-4 align-items-center">
            <div class="col-12 col-md-auto">
                <div class="p-3 bg-light border rounded-2 d-inline-flex" x-html="qrCodeSvg"></div>
            </div>
            <div class="col">
                <p class="small text-secondary mb-2">Scan the QR code with your authenticator app, then enter its current code.</p>
                <p class="small mb-3">Setup key: <code x-text="secretKey"></code></p>

                <form class="d-flex flex-column flex-sm-row gap-2" @submit.prevent="confirmSetup">
                    <div class="flex-grow-1">
                        <label for="two-factor-code" class="visually-hidden">Authentication code</label>
                        <input id="two-factor-code" type="text" inputmode="numeric" autocomplete="one-time-code"
                            class="form-control form-control-sm" placeholder="6-digit code" x-model="code" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm" :disabled="loading">
                        Confirm setup
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="border-top pt-3 mt-3" x-show="recoveryCodes.length > 0">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="h6 fw-semibold mb-0">Recovery codes</h3>
            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" @click="recoveryCodes = []">Hide</button>
        </div>
        <p class="small text-secondary">Store these codes somewhere safe. Each code can only be used once.</p>
        <div class="row g-2">
            <template x-for="recoveryCode in recoveryCodes" :key="recoveryCode">
                <div class="col-6 col-md-3"><code class="d-block p-2 bg-light border rounded-1" x-text="recoveryCode"></code></div>
            </template>
        </div>
    </div>

    <form class="border-top pt-3 mt-3" x-show="passwordAction" @submit.prevent="confirmPasswordAndContinue">
        <label for="two-factor-current-password" class="form-label fw-semibold small">Confirm your password to continue</label>
        <div class="d-flex flex-column flex-sm-row gap-2">
            <input id="two-factor-current-password" type="password" autocomplete="current-password"
                class="form-control form-control-sm" x-model="password" required>
            <button type="submit" class="btn btn-primary btn-sm flex-shrink-0" :disabled="loading">
                <span x-text="loading ? 'Please wait...' : 'Confirm password'"></span>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" @click="passwordAction = null">Cancel</button>
        </div>
    </form>

    <p class="small text-danger mt-3 mb-0" role="alert" x-show="error" x-text="error"></p>
</section>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('twoFactorSettings', () => ({
            enabled: false,
            pending: false,
            passwordAction: null,
            password: '',
            code: '',
            qrCodeSvg: '',
            secretKey: '',
            recoveryCodes: [],
            loading: false,
            error: '',

            init() {
                this.enabled = this.$el.dataset.enabled === '1';
                this.pending = this.$el.dataset.pending === '1';
            },

            requestOptions() {
                return {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                };
            },

            askForPassword(action) {
                this.error = '';
                this.password = '';
                this.passwordAction = action;
            },

            async confirmPasswordAndContinue() {
                this.loading = true;
                this.error = '';
                const action = this.passwordAction;

                try {
                    await axios.post("{{ url('/confirm-password') }}", {
                        password: this.password
                    }, this.requestOptions());

                    this.passwordAction = null;
                    this.password = '';

                    if (action === 'enable') {
                        await this.enableTwoFactor();
                    } else if (action === 'resume') {
                        await this.loadSetupDetails();
                    } else if (action === 'recovery') {
                        await this.loadRecoveryCodes();
                    } else if (action === 'disable') {
                        await this.disableTwoFactor();
                    }
                } catch (error) {
                    this.error = this.firstError(error) || 'Unable to confirm your password. Please try again.';
                } finally {
                    this.loading = false;
                }
            },

            async enableTwoFactor() {
                await axios.post("{{ route('two-factor.enable') }}", {}, this.requestOptions());
                this.pending = true;
                await this.loadSetupDetails();
            },

            async loadSetupDetails() {
                const [qrCode, secret] = await Promise.all([
                    axios.get("{{ route('two-factor.qr-code') }}", this.requestOptions()),
                    axios.get("{{ route('two-factor.secret-key') }}", this.requestOptions()),
                ]);

                this.qrCodeSvg = qrCode.data.svg || '';
                this.secretKey = secret.data.secretKey || '';
            },

            async confirmSetup() {
                this.loading = true;
                this.error = '';

                try {
                    await axios.post("{{ route('two-factor.confirm') }}", {
                        code: this.code
                    }, this.requestOptions());

                    this.enabled = true;
                    this.pending = false;
                    this.code = '';
                    this.qrCodeSvg = '';
                    this.secretKey = '';
                    await this.loadRecoveryCodes();
                } catch (error) {
                    this.error = this.firstError(error) || 'The authentication code could not be confirmed.';
                } finally {
                    this.loading = false;
                }
            },

            async loadRecoveryCodes() {
                const response = await axios.get("{{ route('two-factor.recovery-codes') }}", this.requestOptions());
                this.recoveryCodes = response.data || [];
            },

            async disableTwoFactor() {
                await axios.delete("{{ route('two-factor.disable') }}", this.requestOptions());
                this.enabled = false;
                this.pending = false;
                this.qrCodeSvg = '';
                this.secretKey = '';
                this.recoveryCodes = [];
            },

            firstError(error) {
                const errors = error.response?.data?.errors;

                if (errors) {
                    return Object.values(errors).flat()[0];
                }

                return error.response?.data?.message;
            }
        }));
    });
</script>
@endpush