@props(['type' => 'text', 'name' => null, 'value' => null])
<input type="{{ $type }}" @if($name) name="{{ $name }}" id="{{ $attributes->get('id', $name) }}" value="{{ $type === 'password' ? '' : old($name, $value) }}" @endif
    {{ $attributes->class(['input-shop', 'border-brand-red' => $name && $errors->has($name)]) }}>
