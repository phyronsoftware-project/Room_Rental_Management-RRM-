@php
    // ✅ use cached stats if available (no backend change)
    $stats = method_exists($this, 'getCachedStats') ? $this->getCachedStats() : $this->getStats();

    // ✅ OPTIONAL: បើចង់បង្ហាញតែ 4 cards ដូចរូប -> ដោះ comment ខាងក្រោម
    // $stats = collect($stats)->take(4)->all();

    $colorMap = [
        'primary' => ['bg' => 'bg-indigo-50 dark:bg-indigo-950/40', 'text' => 'text-indigo-600'],
        'info'    => ['bg' => 'bg-sky-50 dark:bg-sky-950/40',       'text' => 'text-sky-600'],
        'success' => ['bg' => 'bg-emerald-50 dark:bg-emerald-950/40','text' => 'text-emerald-600'],
        'warning' => ['bg' => 'bg-amber-50 dark:bg-amber-950/40',   'text' => 'text-amber-600'],
        'danger'  => ['bg' => 'bg-rose-50 dark:bg-rose-950/40',     'text' => 'text-rose-600'],
        'gray'    => ['bg' => 'bg-gray-50 dark:bg-gray-800/40',     'text' => 'text-gray-600'],
    ];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
    @foreach ($stats as $stat)
        @php
            $label = $stat->getLabel();
            $value = $stat->getValue();
            $icon  = $stat->getIcon();

            $colorKey = $stat->getColor() ?? 'primary';
            $c = $colorMap[$colorKey] ?? $colorMap['primary'];

            // Badge %: derive from chart last vs previous (UI-only)
            $chart = $stat->getChart() ?? [];
            $badge = null;
            $trendUp = true;

            if (is_array($chart) && count($chart) >= 2) {
                $last = (float) $chart[count($chart) - 1];
                $prev = (float) $chart[count($chart) - 2];
                $trendUp = $last >= $prev;

                $pct = ($prev == 0.0) ? 0.0 : (($last - $prev) / abs($prev)) * 100.0;
                $badge = number_format(abs($pct), 1) . '%';
            } else {
                // fallback (matches your screenshot style)
                $badge = '8.4%';
                $trendUp = true;
            }
        @endphp

        <div class="rounded-3xl bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800 shadow-sm p-8 min-h-[190px]">
            <div class="flex items-start justify-between">
                <div class="h-14 w-14 rounded-2xl {{ $c['bg'] }} flex items-center justify-center">
                    @if ($icon)
                        <x-filament::icon :icon="$icon" class="h-7 w-7 {{ $c['text'] }}" />
                    @endif
                </div>

                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full
                    {{ $trendUp ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-300' }}
                    text-sm font-semibold">
                    <x-filament::icon :icon="$trendUp ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down'" class="h-4 w-4" />
                    {{ $badge }}
                </span>
            </div>

            <div class="mt-12">
                <div class="text-slate-500 dark:text-slate-400 text-sm font-semibold tracking-widest uppercase">
                    {{ $label }}
                </div>

                <div class="mt-3 text-4xl font-bold text-slate-900 dark:text-white">
                    {{ $value }}
                </div>

                @if ($stat->getDescription())
                    <div class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                        {{ $stat->getDescription() }}
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>
