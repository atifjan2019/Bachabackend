@props(['name' => null, 'value' => null])
<textarea @if($name) name="{{ $name }}" id="{{ $attributes->get('id', $name) }}" @endif
    {{ $attributes->class(['textarea-shop', 'border-brand-red' => $name && $errors->has($name)]) }}>{{ $name ? old($name, $value) : $slot }}</textarea>
