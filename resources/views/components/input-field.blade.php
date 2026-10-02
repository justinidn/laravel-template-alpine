@props([
'name',
'label',
'type' => 'text',
'placeholder' => '',
'required' => false,
'model' => null,
'errorKey' => null,
'allowDecimal' => false
])

@php
$modelName = $model ?? "form.{$name}";
$errorPath = $errorKey ?? $name;
// Tipe input diubah ke 'text' jika number agar kontrol filter Javascript 100% bekerja penuh
$inputType = $type === 'number' ? 'text' : $type;
$xModelDirective = $type === 'number' ? "x-model.number={$modelName}" : "x-model={$modelName}";
@endphp

<div class="mb-3">
    <label for="{{ $name }}" class="form-label fw-semibold">
        {{ $label }}
        @if($required)
        <span class="text-danger">*</span>
        @endif
    </label>

    <input type="{{ $inputType }}"
        id="{{ $name }}"
        class="form-control rounded-0 border-0 border-bottom border-secondary bg-transparent shadow-none px-0"
        :class="{'border-danger': errors?.{{ $errorPath }}}"
        {{ $xModelDirective }}
        placeholder="{{ $placeholder }}"
        @if($type==='number' )
        inputmode="{{ $allowDecimal ? 'decimal' : 'numeric' }}"
        @keypress="if (!{{ $allowDecimal ? '/[0-9.,]/' : '/[0-9]/' }}.test($event.key)) $event.preventDefault()"
        @input="$event.target.value = $event.target.value.replace({{ $allowDecimal ? '/[^0-9.,]/g' : '/[^0-9]/g' }}, '')"
        @endif
        {{ $required ? 'required' : '' }}
        {{ $attributes }}>

    <template x-if="errors?.{{ $errorPath }}">
        <div class="text-danger fs-7 mt-1" x-text="errors.{{ $errorPath }}[0]"></div>
    </template>
</div>