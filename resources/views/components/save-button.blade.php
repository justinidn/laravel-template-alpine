@php
$defaultClass = 'btn btn-primary btn-sm px-4 shadow-sm';
$buttonLabel = $slot->isEmpty() ? 'Save' : $slot;
@endphp

@if(!empty($permissions))
@canany($permissions)
<button type="submit" {{ $attributes->merge(['class' => $defaultClass]) }}>

    <span>{{ $buttonLabel }}</span>
</button>
@endcanany
@else
<button type="submit" {{ $attributes->merge(['class' => $defaultClass]) }}>

    <span>{{ $buttonLabel }}</span>
</button>
@endif