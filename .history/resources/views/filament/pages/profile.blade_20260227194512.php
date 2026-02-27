<x-filament-panels::page>
    {{-- =========================================================
       Module: Profile Page (UI Only)
       Purpose: Clean profile layout using PURE CSS (no Tailwind)
       ========================================================= --}}

   

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
