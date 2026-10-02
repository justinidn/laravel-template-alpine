@props([
'name',
'label',
'options' => null, // Nama variabel Alpine (misal: 'departments')
'items' => null, // Array/Collection dari Laravel Blade (opsional)
'valueKey' => 'id', // Key untuk value jika items berupa objek
'labelKey' => 'name', // Key untuk text jika items berupa objek
'placeholder' => null,
'required' => false,
'model' => null,
'errorKey' => null
])

@php
$modelName = $model ?? "form.{$name}";
$errorPath = $errorKey ?? $name;
$selectPlaceholder = $placeholder ?? "-- Select {$label} --";
@endphp

<div class="mb-3">
    <label for="{{ $name }}" class="form-label fw-semibold">
        {{ $label }}
        @if($required)
        <span class="text-danger">*</span>
        @endif
    </label>

    <select id="{{ $name }}"
        class="form-select rounded-0 border-0 border-bottom border-secondary bg-transparent shadow-none px-0"
        :class="{'border-danger': errors?.{{ $errorPath }}}"
        x-model="{{ $modelName }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes }}>

        <option value="" disabled>{{ $selectPlaceholder }}</option>

        {{-- 1. OPSI DARI ALPINE.JS (x-for) --}}
        @if($options)
        <template x-for="(val, key) in {{ $options }}" :key="key">
            <option :value="typeof val === 'object' ? val.id : key" x-text="typeof val === 'object' ? val.name : val"></option>
        </template>
        @endif

        {{-- 2. OPSI DARI LARAVEL BLADE ($items) --}}
        @if($items)
        @foreach($items as $key => $item)
        @php
        $val = is_array($item) || is_object($item) ? data_get($item, $valueKey) : $key;
        $txt = is_array($item) || is_object($item) ? data_get($item, $labelKey) : $item;
        @endphp
        <option value="{{ $val }}">{{ $txt }}</option>
        @endforeach
        @endif

        {{-- 3. OPSI MANUAL DARI SLOT --}}
        {{ $slot }}
    </select>

    <template x-if="errors?.{{ $errorPath }}">
        <div class="text-danger fs-7 mt-1" x-text="errors.{{ $errorPath }}[0]"></div>
    </template>
</div>