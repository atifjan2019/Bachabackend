@props(['for' => null])
<label @if($for) for="{{ $for }}" @endif {{ $attributes->class(['label-shop']) }}>{{ $slot }}</label>
