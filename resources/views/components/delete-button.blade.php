@php
$defaultClass = 'btn btn-danger btn-sm d-flex align-items-center gap-1 shadow-sm';
$buttonLabel = $slot->isEmpty() ? 'Delete' : $slot;
@endphp

@if($permission)
@can($permission)
<button type="button"
    {{ $attributes->merge(['class' => $defaultClass]) }}
    @click="$dispatch('{{ $event }}', '{{ $id }}')">
    <i class="bi bi-trash"></i>
    <span>{{ $buttonLabel }}</span>
</button>
@endcan
@else
<button type="button"
    {{ $attributes->merge(['class' => $defaultClass]) }}
    @click="$dispatch('{{ $event }}', '{{ $id }}')">
    <i class="bi bi-trash"></i>
    <span>{{ $buttonLabel }}</span>
</button>
@endif