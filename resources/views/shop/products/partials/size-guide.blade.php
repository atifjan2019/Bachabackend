@php
    /** @var array{banded: list<array{band: array, sizes: list<string>}>, other: list<string>} $grouped */
    $isFigure = $sizes !== [] && $grouped['other'] === [] && count($grouped['banded']) >= 1;
@endphp
@if($isFigure)
    <div class="space-y-4">
        <p class="text-xs leading-relaxed text-muted">
            Waistcoat sizes follow your chest measurement. Measure around the fullest part of your chest
            in inches and pick the matching figure size.
        </p>
        <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
            <table class="w-full text-left text-xs min-w-[340px]">
                <thead class="text-brand-black">
                    <tr><th class="py-2 pr-3">Fit</th><th class="py-2 pr-3">Sizes</th><th class="py-2">Fits Chest (in)</th></tr>
                </thead>
                <tbody class="text-muted">
                    @foreach($grouped['banded'] as $g)
                        @php $lo = $g['sizes'][0]; $hi = end($g['sizes']); @endphp
                        <tr class="border-t border-border">
                            <td class="py-1.5 pr-3 font-medium text-brand-black">{{ $g['band']['label'] }}</td>
                            <td class="pr-3">{{ implode(', ', $g['sizes']) }}</td>
                            <td>{{ $lo === $hi ? $lo.'"' : $lo.'"–'.$hi.'"' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
        <table class="w-full text-left text-xs min-w-[340px]">
            <thead class="text-brand-black">
                <tr><th class="py-2 pr-3">Size</th><th class="py-2 pr-3">Chest (in)</th><th class="py-2 pr-3">Waist (in)</th><th class="py-2">Length (in)</th></tr>
            </thead>
            <tbody class="text-muted">
                @foreach([['S', '36–38', '30–32', '27'], ['M', '38–40', '32–34', '28'], ['L', '40–42', '34–36', '29'], ['XL', '42–44', '36–38', '30'], ['XXL', '44–46', '38–40', '31']] as [$s, $chest, $waist, $len])
                    <tr class="border-t border-border"><td class="py-1.5 pr-3">{{ $s }}</td><td class="pr-3">{{ $chest }}</td><td class="pr-3">{{ $waist }}</td><td>{{ $len }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
