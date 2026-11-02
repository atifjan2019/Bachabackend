{{-- Toast viewport (components/ui/toast.tsx) + server flash → toast bridge. --}}
<div class="fixed bottom-4 right-4 flex flex-col gap-2 z-[100] outline-none pointer-events-none" aria-live="polite">
    <template x-for="t in $store.ui.toasts" :key="t.id">
        <div class="pointer-events-auto animate-fade-in bg-ivory border border-border rounded-lg shadow-card-hover p-4 flex gap-3 items-start w-[320px] max-w-[calc(100vw-2rem)]"
             :class="{ 'border-l-4 border-l-green-600': t.tone === 'success', 'border-l-4 border-l-brand-red': t.tone === 'error' }" role="status">
            <div class="flex-1">
                <p class="font-medium text-sm text-brand-black" x-text="t.title"></p>
                <p class="text-xs text-muted mt-1" x-show="t.description" x-text="t.description"></p>
            </div>
            <button type="button" aria-label="Close" class="text-muted hover:text-brand-black" @click="$store.ui.dismiss(t.id)">
                <x-lucide-x class="h-4 w-4" />
            </button>
        </div>
    </template>
</div>

@php
    $flash = session('toast');
    if (is_string($flash)) {
        $flash = ['title' => $flash];
    } elseif (! $flash && session('status')) {
        $flash = ['title' => session('status'), 'tone' => 'success'];
    }
@endphp
@if(is_array($flash) && ! empty($flash['title']))
    <div x-data x-init="$store.ui.toast(@js(['title' => $flash['title'], 'description' => $flash['description'] ?? '', 'tone' => $flash['tone'] ?? 'default']))"></div>
@endif
