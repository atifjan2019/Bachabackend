<section class="mt-16 lg:mt-24" x-data="productReviews(@js(['slug' => $product->slug, 'count' => $reviewCount, 'average' => $reviewAverage]))">
    <div class="mb-8 flex flex-col gap-4 border-b border-ink-10 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.28em] text-brand-red">Reviews</p>
            <h2 class="font-display text-2xl text-brand-black sm:text-3xl">Customer reviews</h2>
            <div class="mt-3 flex items-center gap-3">
                <span class="inline-flex items-center gap-0.5" role="img" :aria-label="`${Math.round(average)} out of 5`">
                    @for($n = 1; $n <= 5; $n++)
                        <x-lucide-star class="h-4 w-4 {{ $n <= round($reviewAverage) ? 'fill-brand-red text-brand-red' : 'fill-transparent text-ink-30' }}" stroke-width="1.5"
                            ::class="{{ $n }} <= Math.round(average) ? 'fill-brand-red text-brand-red' : 'fill-transparent text-ink-30'" />
                    @endfor
                </span>
                <span class="text-sm text-ink-50" x-text="count > 0 ? `${average.toFixed(1)} · ${count} review${count === 1 ? '' : 's'}` : 'No reviews yet'">
                    {{ $reviewCount > 0 ? number_format($reviewAverage, 1).' · '.$reviewCount.' review'.($reviewCount === 1 ? '' : 's') : 'No reviews yet' }}
                </span>
            </div>
        </div>
        <button type="button" x-on:click="open = !open" x-text="open ? 'Close' : 'Write a review'"
                class="inline-flex shrink-0 items-center justify-center border-2 border-brand-black px-6 py-3 text-[12px] font-bold uppercase tracking-[0.18em] text-brand-black transition-colors hover:bg-brand-black hover:text-white">Write a review</button>
    </div>

    <form x-cloak x-show="open" x-on:submit.prevent="submit()" class="mb-10 max-w-xl space-y-4 border border-ink-10 bg-surface-soft p-5 sm:p-6">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-brand-black" for="review-name">Your name</label>
            <input id="review-name" type="text" required x-model="form.author_name" class="w-full border border-ink-10 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand-black">
        </div>
        <div>
            <span class="mb-1.5 block text-sm font-medium text-brand-black">Rating</span>
            <div class="flex items-center gap-1">
                @for($n = 1; $n <= 5; $n++)
                    <button type="button" x-on:click="form.rating = {{ $n }}" x-on:mouseenter="hover = {{ $n }}" x-on:mouseleave="hover = 0" aria-label="{{ $n }} star">
                        <x-lucide-star class="h-6 w-6 transition-colors" stroke-width="1.5" ::class="{{ $n }} <= (hover || form.rating) ? 'fill-brand-red text-brand-red' : 'fill-transparent text-ink-30'" />
                    </button>
                @endfor
            </div>
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-brand-black" for="review-comment">Review</label>
            <textarea id="review-comment" rows="3" x-model="form.comment" placeholder="Share your experience…" class="w-full border border-ink-10 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand-black"></textarea>
        </div>
        <p class="text-xs text-brand-red" x-show="status === 'error'" x-text="error || 'Couldn’t submit your review. Please try again.'"></p>
        <button type="submit" :disabled="status === 'loading'" x-text="status === 'loading' ? 'Submitting…' : 'Submit review'"
                class="inline-flex items-center justify-center bg-brand-red px-6 py-3 text-[12px] font-bold uppercase tracking-[0.18em] text-white transition-colors hover:bg-brand-red-dark disabled:opacity-70">Submit review</button>
    </form>

    <p x-cloak x-show="status === 'success' && !open" class="mb-6 border border-brand-red/30 bg-brand-red/5 px-4 py-3 text-sm text-brand-black">Thank you! Your review has been posted.</p>

    <ul class="grid gap-5 sm:grid-cols-2" x-show="count > 0">
        {{-- Reviews posted in this session appear first --}}
        <template x-for="r in added" :key="r.id">
            <li class="border border-ink-10 p-5">
                <div class="flex items-center justify-between gap-3">
                    <span class="font-semibold text-brand-black" x-text="r.author_name"></span>
                    <span class="inline-flex items-center gap-0.5" role="img" :aria-label="`${r.rating} out of 5`">
                        <template x-for="n in 5" :key="n">
                            <x-lucide-star class="h-4 w-4" stroke-width="1.5" ::class="n <= r.rating ? 'fill-brand-red text-brand-red' : 'fill-transparent text-ink-30'" />
                        </template>
                    </span>
                </div>
                <p class="mt-3 text-sm leading-relaxed text-ink-70" x-show="r.comment" x-text="r.comment"></p>
            </li>
        </template>
        @foreach($reviews as $r)
            <li class="border border-ink-10 p-5">
                <div class="flex items-center justify-between gap-3">
                    <span class="font-semibold text-brand-black">{{ $r->author_name }}</span>
                    <span class="inline-flex items-center gap-0.5" role="img" aria-label="{{ $r->rating }} out of 5">
                        @for($n = 1; $n <= 5; $n++)
                            <x-lucide-star class="h-4 w-4 {{ $n <= $r->rating ? 'fill-brand-red text-brand-red' : 'fill-transparent text-ink-30' }}" stroke-width="1.5" />
                        @endfor
                    </span>
                </div>
                @if($r->comment)
                    <p class="mt-3 text-sm leading-relaxed text-ink-70">{{ $r->comment }}</p>
                @endif
            </li>
        @endforeach
    </ul>
    <p class="text-sm text-ink-50" x-show="count === 0" @if($reviewCount > 0) x-cloak @endif>Be the first to review this product.</p>
</section>
