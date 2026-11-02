@props(['name' => null, 'message' => null])
@if($name)
    @error($name)
        <p {{ $attributes->class(['text-xs text-brand-red mt-1']) }}>{{ $message }}</p>
    @enderror
@elseif($message)
    <p {{ $attributes->class(['text-xs text-brand-red mt-1']) }}>{{ $message }}</p>
@endif
