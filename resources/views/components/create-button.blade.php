@php
$defaultClass = 'btn btn-primary btn-sm d-flex align-items-center gap-2 shadow-sm';
$defaultLabel = 'Add Data';
$buttonLabel = $slot->isEmpty() ? $defaultLabel : $slot;
$hasPermissionCheck = !empty($permission);
@endphp

@if($hasPermissionCheck)
@can($permission)
<button type="button"
    class="{{ $defaultClass }}"
    @click="$dispatch('{{ $event }}')">
    <i class="bi bi-file-earmark-plus"></i>
    <span>{{ $buttonLabel }}</span>
</button>
@endcan
@else
<button type="button"
    class="{{ $defaultClass }}"
    @click="$dispatch('{{ $event }}')">
    <i class="bi bi-file-earmark-plus"></i>
    <span>{{ $buttonLabel }}</span>
</button>
@endif