@props(['title', 'subtitle' => null])
<div class="container-shop py-12 lg:py-20">
    <div class="max-w-md mx-auto bg-ivory border border-border rounded-lg p-6 lg:p-8">
        <div class="text-center mb-6">
            <h1 class="font-display text-3xl text-brand-black">{{ $title }}</h1>
            @if($subtitle)
                <p class="text-sm text-muted mt-1">{{ $subtitle }}</p>
            @endif
        </div>
        {{ $slot }}
        @isset($footer)
            <p class="text-sm text-muted text-center mt-6">{{ $footer }}</p>
        @endisset
    </div>
</div>
