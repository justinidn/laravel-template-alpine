@props([
'name',
'label',
'model' => null,
'showIf' => null,
'bindModel' => true,
'idExpression' => null,
'labelExpression' => null,
'wrapperClass' => 'form-check form-switch mt-3',
'labelClass' => 'form-check-label fw-semibold'
])

@php
$modelName = $model ?? "form.{$name}";
@endphp

@if($showIf)
<template x-if="{{ $showIf }}">
    <div class="{{ $wrapperClass }}">
        <input {{ $attributes->merge(['class' => 'form-check-input']) }} type="checkbox" role="switch" id="{{ $name }}" @if($idExpression) x-bind:id="{{ $idExpression }}" @endif @if($bindModel) x-model="{{ $modelName }}" @endif>
        <label class="{{ $labelClass }}" for="{{ $name }}" @if($idExpression) x-bind:for="{{ $idExpression }}" @endif @if($labelExpression) x-text="{{ $labelExpression }}" @endif>{{ $label }}</label>
    </div>
</template>
@else
<div class="{{ $wrapperClass }}">
    <input {{ $attributes->merge(['class' => 'form-check-input']) }} type="checkbox" role="switch" id="{{ $name }}" @if($idExpression) x-bind:id="{{ $idExpression }}" @endif @if($bindModel) x-model="{{ $modelName }}" @endif>
    <label class="{{ $labelClass }}" for="{{ $name }}" @if($idExpression) x-bind:for="{{ $idExpression }}" @endif @if($labelExpression) x-text="{{ $labelExpression }}" @endif>{{ $label }}</label>
</div>
@endif