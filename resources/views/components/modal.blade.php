@props([
'name',
'show' => false,
'maxWidth' => '2xl'
])

@php
$maxWidth = [
'sm' => 'modal-sm',
'md' => '',
'lg' => 'modal-lg',
'xl' => 'modal-xl',
'2xl' => 'modal-lg',
][$maxWidth] ?? 'modal-lg';
@endphp

<div
    x-data="{ show: @js($show) }"
    x-init="
        const modal = bootstrap.Modal.getOrCreateInstance($el);
        $el.addEventListener('shown.bs.modal', () => {
            @if($attributes->has('focusable'))
                $el.querySelector('input:not([type=hidden]), textarea, select, button')?.focus();
            @endif
        });
        $el.addEventListener('hidden.bs.modal', () => show = false);
        if (show) modal.show();
        $watch('show', value => value ? modal.show() : modal.hide());
    "
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close.stop="show = false"
    class="modal fade"
    id="{{ $name }}"
    tabindex="-1"
    aria-labelledby="{{ $name }}-label"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered {{ $maxWidth }}">
        <div class="modal-content shadow-lg border-0">
            {{ $slot }}
        </div>
    </div>
</div>