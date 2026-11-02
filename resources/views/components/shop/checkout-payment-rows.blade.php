@props(['rows', 'method'])
@php $filled = array_values(array_filter($rows, fn ($r) => isset($r['value']) && trim((string) $r['value']) !== '')); @endphp
<div class="space-y-2">
    @if($filled)
        <dl class="rounded-md bg-ink-5 p-3 text-xs">
            @foreach($filled as $i => $r)
                @php $val = trim((string) $r['value']); $key = $method.'-'.$i; @endphp
                <div class="flex items-center justify-between gap-4 py-1">
                    <dt class="text-muted">{{ $r['label'] }}</dt>
                    <dd class="text-right">
                        @if(! empty($r['copy']))
                            <span class="inline-flex items-center gap-2">
                                <span class="select-all font-semibold text-brand-black">{{ $val }}</span>
                                <button type="button" @click.prevent.stop="copy(@js($val), @js($key))"
                                        :aria-label="copied[@js($key)] ? 'Copied' : 'Copy {{ $val }}'" :title="copied[@js($key)] ? 'Copied!' : 'Copy'"
                                        class="inline-flex h-6 shrink-0 items-center gap-1 rounded border px-1.5 text-[10px] font-bold uppercase tracking-wide transition-colors"
                                        :class="copied[@js($key)] ? 'border-emerald-200 bg-emerald-50 text-emerald-600' : 'border-ink-10 bg-white text-ink-50 hover:border-brand-red hover:text-brand-red'">
                                    <x-lucide-check class="h-3 w-3" :x-show="'copied['.json_encode($key).']'" />
                                    <x-lucide-copy class="h-3 w-3" :x-show="'!copied['.json_encode($key).']'" />
                                    <span x-text="copied[@js($key)] ? 'Copied' : 'Copy'"></span>
                                </button>
                            </span>
                        @else
                            <span class="select-all font-semibold text-brand-black">{{ $val }}</span>
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
    @else
        <p class="text-xs text-muted">Payment details will be shared after you place the order.</p>
    @endif
    <p class="text-xs font-medium text-brand-red">After paying, upload your payment receipt/screenshot below before placing the order.</p>
</div>
