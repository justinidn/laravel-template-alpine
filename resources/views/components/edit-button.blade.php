@php
$defaultClass = 'btn btn-warning btn-sm d-flex align-items-center gap-1 shadow-sm';
$buttonLabel = $slot->isEmpty() ? 'Edit' : $slot;
@endphp

@if($permission)
@can($permission)
<button type="button"
    {{ $attributes->merge(['class' => $defaultClass]) }}
    @click="$dispatch('{{ $event }}', '{{ $id }}')">
    <i class="bi bi-pencil-square"></i>
    <span>{{ $buttonLabel }}</span>
</button>
@endcan
@else
<button type="button"
    {{ $attributes->merge(['class' => $defaultClass]) }}
    @click="$dispatch('{{ $event }}', '{{ $id }}')">
    <i class="bi bi-pencil-square"></i>
    <span>{{ $buttonLabel }}</span>
</button>
@endif