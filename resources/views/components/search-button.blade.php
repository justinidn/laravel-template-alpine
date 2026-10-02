<button type="button"
    class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2 shadow-sm"
    @click="$dispatch('{{ $event }}')">
    <i class="bi bi-search me-1"></i> {{ $slot->isEmpty() ? $label : $slot }}
</button>