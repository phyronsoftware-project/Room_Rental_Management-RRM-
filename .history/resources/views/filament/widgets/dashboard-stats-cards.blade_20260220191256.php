@php
    // ✅ NO backend change: use existing stats
    $stats = method_exists($this, 'getCachedStats') ? $this->getCachedStats() : $this->getStats();
@endphp

<style>
    /* ===== Dashboard Stat Cards (CSS Only) ===== */
    .rrm-stat-grid{
        display:grid;
        grid-template-columns:repeat(1,minmax(0,1fr));
        gap:24px;
    }
    @media (min-width:640px){
        .rrm-stat-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); }
    }
    @media (min-width:1280px){
        .rrm-stat-grid{ grid-template-columns:repeat(4,minmax(0,1fr)); }
    }

    .rrm-stat-card{
        border-radius:28px;
        background:#ffffff;
        border:1px solid #eef2f7;
        box-shadow:0 10px 26px rgba(15,23,42,.08);
        padding:32px;
        min-height:190px;
    }
    .dark .rrm-stat-card{
        background:#0b1220;
        border-color:rgba(148,163,184,.18);
        box-shadow:none;
    }

    .rrm-stat-top{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:14px;
    }

    .rrm-stat-icon{
        width:56px;
        height:56px;
        border-radius:18px;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#4f46e5;
        background:#eef2ff;
    }
    .dark .rrm-stat-icon{ background:rgba(79,70,229,.16); }

    /* color mapping by data-color */
    .rrm-stat-icon[data-color="primary"]{ color:#4f46e5; background:#eef2ff; }
    .rrm-stat-icon[data-color="success"]{ color:#059669; background:#ecfdf5; }
    .rrm-stat-icon[data-color="info"]{ color:#0284c7; background:#eff6ff; }
    .rrm-stat-icon[data-color="warning"]{ color:#d97706; background:#fffbeb; }
    .rrm-stat-icon[data-color="danger"]{ color:#dc2626; background:#fef2f2; }
    .rrm-stat-icon[data-color="gray"]{ color:#4b5563; background:#f3f4f6; }

    .dark .rrm-stat-icon[data-color="primary"]{ background:rgba(79,70,229,.16); }
    .dark .rrm-stat-icon[data-color="success"]{ background:rgba(5,150,105,.16); }
    .dark .rrm-stat-icon[data-color="info"]{ background:rgba(2,132,199,.16); }
    .dark .rrm-stat-icon[data-color="warning"]{ background:rgba(217,119,6,.16); }
    .dark .rrm-stat-icon[data-color="danger"]{ background:rgba(220,38,38,.16); }
    .dark .rrm-stat-icon[data-color="gray"]{ background:rgba(148,163,184,.12); }

    .rrm-stat-icon svg{ width:28px; height:28px; }

    .rrm-badge{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:6px 12px;
        border-radius:999px;
        font-weight:700;
        font-size:14px;
        line-height:1;
        color:#059669;
        background:#ecfdf5;
    }
    .dark .rrm-badge{
        color:#86efac;
        background:rgba(5,150,105,.16);
    }
    .rrm-badge svg{ width:16px; height:16px; }

    .rrm-stat-body{ margin-top:48px; }

    .rrm-stat-label{
        font-size:14px;
        font-weight:700;
        letter-spacing:.12em;
        text-transform:uppercase;
        color:#64748b;
    }
    .dark .rrm-stat-label{ color:rgba(148,163,184,.9); }

    .rrm-stat-value{
        margin-top:10px;
        font-size:44px;
        font-weight:800;
        color:#0f172a;
    }
    .dark .rrm-stat-value{ color:#ffffff; }
</style>

<div class="rrm-stat-grid">
    @foreach ($stats as $stat)
        @php
            $label = $stat->getLabel();
            $value = $stat->getValue();
            $icon  = $stat->getIcon();
            $color = $stat->getColor() ?? 'primary'; // Filament color key
        @endphp

        <div class="rrm-stat-card">
            <div class="rrm-stat-top">
                <div class="rrm-stat-icon" data-color="{{ $color }}">
                    @if ($icon)
                        <x-filament::icon :icon="$icon" />
                    @endif
                </div>

                {{-- ✅ UI only badge (ដូចរូប) --}}
                <span class="rrm-badge">
                    <x-filament::icon icon="heroicon-m-arrow-trending-up" />
                    8.4%
                </span>
            </div>

            <div class="rrm-stat-body">
                <div class="rrm-stat-label">{{ $label }}</div>
                <div class="rrm-stat-value">{{ $value }}</div>
            </div>
        </div>
    @endforeach
</div>
