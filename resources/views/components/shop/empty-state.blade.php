@props(['icon' => null, 'title', 'description' => null, 'ctaHref' => null, 'ctaLabel' => null])
<div {{ $attributes->class(['flex flex-col items-center text-center py-16 px-4']) }}>
    @if($icon)
        <div class="mb-4 text-gold">
            <x-dynamic-component :component="'lucide-'.$icon" class="h-10 w-10" />
        </div>
    @endif
    <h3 class="font-display text-2xl text-brand-black">{{ $title }}</h3>
    @if($description)
        <p class="mt-2 text-sm text-muted max-w-md">{{ $description }}</p>
    @endif
    @if($ctaHref && $ctaLabel)
        <x-shop.button :href="$ctaHref" class="mt-6">{{ $ctaLabel }}</x-shop.button>
    @endif
    {{ $slot }}
</div>
