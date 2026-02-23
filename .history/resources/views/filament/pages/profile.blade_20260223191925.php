<x-filament-panels::page>
    {{-- =========================================================
       Module: Profile Page (UI Only)
       Purpose: Clean profile layout using PURE CSS (no Tailwind)
       ========================================================= --}}

    <style>
        /* ===== Profile Page (Scoped CSS) ===== */
        .pf {
            --pf-bg: #ffffff;
            --pf-border: #e2e8f0;
            --pf-text: #0f172a;
            --pf-muted: #64748b;
            --pf-soft: #f8fafc;

            --pf-danger: #ef4444;
            --pf-danger-bg: #fee2e2;
            --pf-warning: #f59e0b;
            --pf-warning-bg: #fef3c7;
            --pf-success: #22c55e;
            --pf-success-bg: #dcfce7;
            --pf-gray: #64748b;
            --pf-gray-bg: #f1f5f9;

            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji",
                "Segoe UI Emoji";
            color: var(--pf-text);
        }

        .pf * { box-sizing: border-box; }

        .pf-stack > * + * { margin-top: 18px; }

        .pf-card {
            background: var(--pf-bg);
            border: 1px solid var(--pf-border);
            border-radius: 18px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }

        .pf-hero-bg {
            height: 110px;
            /* background: linear-gradient(90deg, rgba(59, 130, 246, 0.18), rgba(59, 130, 246, 0.06), rgba(255, 255, 255, 0)); */
        }

        .pf-hero {
            padding: 18px 18px 16px;
            margin-top: -70px;
        }

        .pf-hero-row {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }

        .pf-identity {
            display: flex;
            gap: 14px;
            align-items: center;
            min-width: 260px;
            flex: 1;
        }

        .pf-avatar {
            width: 64px;
            height: 64px;
            border-radius: 999px;
            object-fit: cover;
            border: 1px solid var(--pf-border);
            background: var(--pf-soft);
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.08);
        }

        .pf-avatar-fallback {
            width: 64px;
            height: 64px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--pf-border);
            background: var(--pf-gray-bg);
            font-weight: 800;
            font-size: 22px;
        }

        .pf-name-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            min-width: 0;
        }

        .pf-title {
            margin: 0;
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -0.02em;
            line-height: 1.15;
            max-width: 100%;
        }

        .pf-meta {
            margin-top: 6px;
            font-size: 13px;
            color: var(--pf-muted);
            line-height: 1.5;
        }

        .pf-meta strong { color: var(--pf-text); font-weight: 800; }

        .pf-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .pf-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 12px;
            border: 1px solid transparent;
            font-weight: 800;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            user-select: none;
            transition: transform .05s ease, background .15s ease, border-color .15s ease, opacity .15s ease;
        }

        .pf-btn:active { transform: translateY(1px); }

        .pf-btn--gray {
            background: #fff;
            border-color: var(--pf-border);
            color: var(--pf-text);
        }

        .pf-btn--gray:hover { background: var(--pf-soft); }

        .pf-btn--danger {
            background: var(--pf-danger);
            border-color: var(--pf-danger);
            color: #fff;
        }

        .pf-btn--danger:hover { opacity: .92; }

        .pf-icon {
            width: 18px;
            height: 18px;
            display: inline-block;
            color: rgba(100, 116, 139, .95);
        }

        .pf-icon svg {
            width: 18px;
            height: 18px;
            display: block;
            stroke: currentColor;
        }

        .pf-badge {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .08em;
            border: 1px solid transparent;
            text-transform: uppercase;
            line-height: 1;
        }

        .pf-badge--danger { color: var(--pf-danger); background: var(--pf-danger-bg); border-color: rgba(239, 68, 68, .25); }
        .pf-badge--warning { color: #92400e; background: var(--pf-warning-bg); border-color: rgba(245, 158, 11, .25); }
        .pf-badge--success { color: #166534; background: var(--pf-success-bg); border-color: rgba(34, 197, 94, .25); }
        .pf-badge--gray { color: var(--pf-gray); background: var(--pf-gray-bg); border-color: rgba(100, 116, 139, .25); }

        .pf-stats {
            margin-top: 14px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        @media (min-width: 640px) {
            .pf-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (min-width: 1024px) {
            .pf-stats { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        .pf-stat {
            border: 1px solid var(--pf-border);
            background: rgba(255, 255, 255, .85);
            border-radius: 14px;
            padding: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .pf-stat .label {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .10em;
            text-transform: uppercase;
            color: var(--pf-muted);
        }

        .pf-stat .value {
            margin-top: 6px;
            font-size: 13px;
            font-weight: 900;
            color: var(--pf-text);
        }

        .pf-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        @media (min-width: 1024px) {
            .pf-grid { grid-template-columns: 2fr 1fr; }
        }

        .pf-section {
            padding: 16px;
        }

        .pf-section h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 900;
            letter-spacing: -0.01em;
        }

        .pf-section p {
            margin: 6px 0 0;
            font-size: 13px;
            color: var(--pf-muted);
            line-height: 1.5;
        }

        .pf-dl {
            margin-top: 14px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        @media (min-width: 640px) {
            .pf-dl { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        .pf-kv {
            border: 1px solid var(--pf-border);
            border-radius: 14px;
            padding: 14px;
            background: #fff;
        }

        .pf-kv dt {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .10em;
            text-transform: uppercase;
            color: var(--pf-muted);
        }

        .pf-kv dd {
            margin: 8px 0 0;
            font-size: 13px;
            font-weight: 900;
            color: var(--pf-text);
        }

        .pf-kv dd .pf-badge { margin-top: 2px; }

        .pf-side-stack > * + * { margin-top: 12px; }

        .pf-note {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .pf-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 12px;
            padding: 2px 7px;
            border-radius: 9px;
            background: var(--pf-soft);
            border: 1px solid var(--pf-border);
        }
    </style>

    @php
        $u = auth()->user();

        $avatarUrl = null;
        if (! empty($u->profile_image_url)) {
            $avatarUrl = str_starts_with($u->profile_image_url, 'http')
                ? $u->profile_image_url
                : \Illuminate\Support\Facades\Storage::disk('public')->url($u->profile_image_url);
        }

        $role = strtolower((string) ($u->role ?? ''));

        $roleVariant = match ($role) {
            'super_admin' => 'danger',
            'manager'     => 'warning',
            'owner'       => 'success',
            default       => 'gray',
        };

        $roleLabel = $u->role ? strtoupper(str_replace('_', ' ', $u->role)) : '—';

        $propertyName = $u->property?->name ?? null;

        $fmt = fn($dt) => $dt ? $dt->format('d M Y, H:i') : '—';

        $initial = strtoupper(mb_substr($u->full_name ?: ($u->email ?: 'U'), 0, 1));

        $otpHas = ! empty($u->otp_code);
        $otpExpiry = $u->otp_expiry ?? null;
        $otpHuman = $otpExpiry ? $otpExpiry->diffForHumans() : null;
    @endphp

    <div class="pf pf-stack">

        {{-- HERO --}}
        <div class="pf-card">
            <div class="pf-hero-bg"></div>

            <div class="pf-hero">
                <div class="pf-hero-row">
                    <div class="pf-identity">
                        @if ($avatarUrl)
                            <img class="pf-avatar" src="{{ $avatarUrl }}" alt="{{ $u->full_name }}" />
                        @else
                            <div class="pf-avatar-fallback">{{ $initial }}</div>
                        @endif

                        <div style="min-width:0;">
                            <div class="pf-name-row">
                                <h1 class="pf-title">{{ $u->full_name ?? '—' }}</h1>
                                <span class="pf-badge pf-badge--{{ $roleVariant }}">{{ $roleLabel }}</span>
                            </div>

                            <div class="pf-meta">
                                <div><strong>Email:</strong> {{ $u->email ?? '—' }}</div>
                                @if ($propertyName || $u->property_id)
                                    <div><strong>Property:</strong> {{ $propertyName ?? ('#' . $u->property_id) }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="pf-actions">
                        <a class="pf-btn pf-btn--gray" href="{{ route('filament.admin.pages.dashboard') }}">
                            <span class="pf-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 10.5 12 3l9 7.5V21a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 21V10.5z"/>
                                    <path d="M9 22V12h6v10"/>
                                </svg>
                            </span>
                            Dashboard
                        </a>

                        <form method="post" action="{{ filament()->getLogoutUrl() }}" style="display:inline;">
                            @csrf
                            <button class="pf-btn pf-btn--danger" type="submit">
                                <span class="pf-icon" aria-hidden="true" style="color:#fff;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10 17l5-5-5-5"/>
                                        <path d="M15 12H3"/>
                                        <path d="M21 21V3a1 1 0 0 0-1-1h-6"/>
                                    </svg>
                                </span>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>

                {{-- STATS --}}
                <div class="pf-stats">
                    <div class="pf-stat">
                        <div>
                            <div class="label">User ID</div>
                            <div class="value">{{ $u->user_id ?? '—' }}</div>
                        </div>
                        <span class="pf-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M7 7a4 4 0 1 0 8 0 4 4 0 0 0-8 0z"/>
                                <path d="M4 21a8 8 0 0 1 16 0"/>
                            </svg>
                        </span>
                    </div>

                    <div class="pf-stat">
                        <div>
                            <div class="label">Property ID</div>
                            <div class="value">{{ $u->property_id ?? '—' }}</div>
                        </div>
                        <span class="pf-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 21V8l9-5 9 5v13"/>
                                <path d="M9 21V12h6v9"/>
                            </svg>
                        </span>
                    </div>

                    <div class="pf-stat">
                        <div>
                            <div class="label">Created</div>
                            <div class="value">{{ $fmt($u->created_at) }}</div>
                        </div>
                        <span class="pf-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8 3v3"/>
                                <path d="M16 3v3"/>
                                <path d="M4 8h16"/>
                                <path d="M6 6h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"/>
                            </svg>
                        </span>
                    </div>

                    <div class="pf-stat">
                        <div>
                            <div class="label">Updated</div>
                            <div class="value">{{ $fmt($u->updated_at) }}</div>
                        </div>
                        <span class="pf-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 12a9 9 0 1 1-3-6.7"/>
                                <path d="M21 3v6h-6"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- DETAILS GRID --}}
        <div class="pf-grid">

            {{-- Account Details --}}
            <div class="pf-card pf-section">
                <h2>Account Details</h2>
                <p>Basic information about this account.</p>

                <dl class="pf-dl">
                    <div class="pf-kv">
                        <dt>Full Name</dt>
                        <dd>{{ $u->full_name ?? '—' }}</dd>
                    </div>

                    <div class="pf-kv">
                        <dt>Email</dt>
                        <dd>{{ $u->email ?? '—' }}</dd>
                    </div>

                    <div class="pf-kv">
                        <dt>Role</dt>
                        <dd>
                            <span class="pf-badge pf-badge--{{ $roleVariant }}">{{ $roleLabel }}</span>
                        </dd>
                    </div>

                    <div class="pf-kv">
                        <dt>Property</dt>
                        <dd>{{ $propertyName ?? ($u->property_id ? ('#'.$u->property_id) : '—') }}</dd>
                    </div>

                    <div class="pf-kv">
                        <dt>User ID</dt>
                        <dd>{{ $u->user_id ?? '—' }}</dd>
                    </div>

                    <div class="pf-kv">
                        <dt>Password</dt>
                        <dd>✅ Set</dd>
                    </div>
                </dl>
            </div>

            {{-- Security & Activity --}}
            <div class="pf-card pf-section">
                <h2>Security & Activity</h2>
                <p>OTP and account timestamps.</p>

                <div class="pf-side-stack" style="margin-top:14px;">
                    <div class="pf-kv">
                        <dt>OTP Status</dt>
                        <dd>
                            @if ($otpHas)
                                ✅ Has OTP
                                @if ($otpHuman)
                                    <span style="font-size:12px; font-weight:700; color: var(--pf-muted);">
                                        (expires {{ $otpHuman }})
                                    </span>
                                @endif
                            @else
                                —
                            @endif
                        </dd>
                    </div>

                    <div class="pf-kv">
                        <dt>OTP Expiry</dt>
                        <dd>{{ $fmt($otpExpiry) }}</dd>
                    </div>

                    <div class="pf-kv">
                        <dt>Created At</dt>
                        <dd>{{ $fmt($u->created_at) }}</dd>
                    </div>

                    <div class="pf-kv">
                        <dt>Updated At</dt>
                        <dd>{{ $fmt($u->updated_at) }}</dd>
                    </div>
                </div>
            </div>

        </div>

        {{-- Note --}}
        <div class="pf-card pf-section">
            <div class="pf-note">
                <span class="pf-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22a10 10 0 1 0-10-10 10 10 0 0 0 10 10z"/>
                        <path d="M12 16v-5"/>
                        <path d="M12 8h.01"/>
                    </svg>
                </span>
                <div>
                    <div style="font-weight:900;">Note</div>
                    <div style="margin-top:6px; font-size:13px; color: var(--pf-muted); line-height:1.6;">
                        If profile images don’t display, run
                        <span class="pf-code">php artisan storage:link</span>
                        and ensure images are stored on the
                        <span class="pf-code">public</span> disk.
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
