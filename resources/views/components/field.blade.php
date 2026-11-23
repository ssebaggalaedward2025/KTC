@props(['name', 'label', 'type' => 'text', 'bag' => 'default', 'hint' => null])
@php($error = $errors->getBag($bag)->first($name))
<div {{ $attributes->only('class')->merge(['class' => 'mb-4']) }}>
    <label for="{{ $name }}" class="label">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
           @if ($type !== 'password') value="{{ old($name, $attributes->get('value')) }}" @endif
           {{ $attributes->except(['class', 'value'])->merge(['class' => 'input']) }}
           @if ($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif>
    @if ($hint && ! $error)
        <p class="mt-1 text-sm text-gray-500">{{ $hint }}</p>
    @endif
    @if ($error)
        <p id="{{ $name }}-error" class="mt-1 text-sm text-red-700">{{ $error }}</p>
    @endif
</div>
