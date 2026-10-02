<x-guest-layout>
    <div class="card border shadow-sm rounded-3">
        <div class="card-header bg-light text-center border-bottom py-3">
            <i class="bi bi-person-badge-fill text-primary display-5"></i>
            <h4 class="fw-bold mt-2 mb-0">{{ config('app.name', 'HRIS System') }}</h4>
        </div>

        <div class="card-body p-4">
            @if (session('status'))
            <div class="alert alert-success small mb-3">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="login" class="form-label fw-semibold">Email / NRK</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" id="login" name="login" class="form-control @error('login') is-invalid @enderror" value="{{ old('login') }}" required autofocus>
                    </div>
                    <span class="form-text text-muted small">Masuk menggunakan Email atau NRK Anda</span>
                    @error('login')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3" x-data="{ showPassword: false }">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        <button type="button" class="btn btn-outline-secondary" @click="showPassword = !showPassword">
                            <i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                        </button>
                    </div>
                    @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
                        <label class="form-check-label small" for="remember_me">Ingat Saya</label>
                    </div>
                    <!-- @if (Route::has('password.request'))
                    <a class="text-decoration-none small" href="{{ route('password.request') }}">Lupa Password?</a>
                    @endif -->
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>