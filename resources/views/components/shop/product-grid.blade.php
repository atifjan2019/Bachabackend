@props(['products'])
<div {{ $attributes->class(['grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-5 lg:gap-6']) }}>
    @foreach($products as $product)
        <x-shop.product-card :product="$product" />
    @endforeach
</div>
