<x-filament-panels::page>
    @php
        $u = auth()->user();

        $avatarUrl = null;
        if (! empty($u->profile_image_url)) {
            $avatarUrl = str_starts_with($u->profile_image_url, 'http')
                ? $u->profile_image_url
                : \Illuminate\Support\Facades\Storage::disk('public')->url($u->profile_image_url);
        }

        $role = strtolower((string) ($u->role ?? ''));
        $roleColor = match ($role) {
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

    <div class="space-y-6">
        {{-- HERO HEADER (Clean / Premium) --}}
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
            {{-- subtle header background --}}
            <div class="absolute inset-x-0 top-0 h-28 bg-gradient-to-r from-primary-600/15 via-primary-500/5 to-transparent dark:from-primary-500/10"></div>

            <div class="relative p-6">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-4 min-w-0">
                        {{-- Avatar --}}
                        <div class="shrink-0">
                            @if ($avatarUrl)
                                <img
                                    src="{{ $avatarUrl }}"
                                    alt="{{ $u->full_name }}"
                                    class="h-16 w-16 rounded-full object-cover ring-2 ring-white dark:ring-gray-900 border border-gray-200 dark:border-gray-700"
                                />
                            @else
                                <div
                                    class="h-16 w-16 rounded-full flex items-center justify-center
                                           bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100
                                           border border-gray-200 dark:border-gray-700"
                                >
                                    <span class="text-xl font-bold">{{ $initial }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Name + meta --}}
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="text-2xl font-bold tracking-tight truncate text-gray-900 dark:text-gray-100">
                                    {{ $u->full_name ?? '—' }}
                                </h1>

                                <x-filament::badge :color="$roleColor">
                                    {{ $roleLabel }}
                                </x-filament::badge>
                            </div>

                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-300 truncate">
                                <span class="font-medium">Email:</span>
                                <span class="font-semibold">{{ $u->email ?? '—' }}</span>
                            </div>

                            @if ($propertyName || $u->property_id)
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-300 truncate">
                                    <span class="font-medium">Property:</span>
                                    <span class="font-semibold">
                                        {{ $propertyName ?? ('#' . $u->property_id) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-wrap gap-2">
                        <x-filament::button
                            tag="a"
                            href="{{ route('filament.admin.pages.dashboard') }}"
                            color="gray"
                            icon="heroicon-m-home"
                        >
                            Dashboard
                        </x-filament::button>

                        <form method="post" action="{{ filament()->getLogoutUrl() }}">
                            @csrf
                            <x-filament::button
                                type="submit"
                                color="danger"
                                icon="heroicon-m-arrow-left-on-rectangle"
                            >
                                Logout
                            </x-filament::button>
                        </form>
                    </div>
                </div>

                {{-- Summary cards row --}}
                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-4 bg-white/60 dark:bg-gray-900/40">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">User ID</div>
                                <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $u->user_id ?? '—' }}</div>
                            </div>
                            <x-filament::icon icon="heroicon-m-identification" class="h-5 w-5 text-gray-400" />
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-4 bg-white/60 dark:bg-gray-900/40">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Property ID</div>
                                <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $u->property_id ?? '—' }}</div>
                            </div>
                            <x-filament::icon icon="heroicon-m-building-office-2" class="h-5 w-5 text-gray-400" />
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-4 bg-white/60 dark:bg-gray-900/40">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Created</div>
                                <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $fmt($u->created_at) }}</div>
                            </div>
                            <x-filament::icon icon="heroicon-m-calendar-days" class="h-5 w-5 text-gray-400" />
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-4 bg-white/60 dark:bg-gray-900/40">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Updated</div>
                                <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $fmt($u->updated_at) }}</div>
                            </div>
                            <x-filament::icon icon="heroicon-m-arrow-path" class="h-5 w-5 text-gray-400" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DETAILS GRID --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Account Details --}}
            <x-filament::section class="lg:col-span-2">
                <x-slot name="heading">Account Details</x-slot>
                <x-slot name="description">Basic information about this account.</x-slot>

                <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @php
                        $box = "rounded-xl border border-gray-200 dark:border-gray-800 p-4";
                        $dtc = "text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400";
                        $ddc = "mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100";
                    @endphp

                    <div class="{{ $box }}">
                        <dt class="{{ $dtc }}">Full Name</dt>
                        <dd class="{{ $ddc }}">{{ $u->full_name ?? '—' }}</dd>
                    </div>

                    <div class="{{ $box }}">
                        <dt class="{{ $dtc }}">Email</dt>
                        <dd class="{{ $ddc }}">{{ $u->email ?? '—' }}</dd>
                    </div>

                    <div class="{{ $box }}">
                        <dt class="{{ $dtc }}">Role</dt>
                        <dd class="mt-1">
                            <x-filament::badge :color="$roleColor">{{ $roleLabel }}</x-filament::badge>
                        </dd>
                    </div>

                    <div class="{{ $box }}">
                        <dt class="{{ $dtc }}">Property</dt>
                        <dd class="{{ $ddc }}">{{ $propertyName ?? ($u->property_id ? ('#'.$u->property_id) : '—') }}</dd>
                    </div>

                    <div class="{{ $box }}">
                        <dt class="{{ $dtc }}">User ID</dt>
                        <dd class="{{ $ddc }}">{{ $u->user_id ?? '—' }}</dd>
                    </div>

                    <div class="{{ $box }}">
                        <dt class="{{ $dtc }}">Password</dt>
                        <dd class="{{ $ddc }}">✅ Set</dd>
                    </div>
                </dl>
            </x-filament::section>

            {{-- Security & Activity --}}
            <x-filament::section>
                <x-slot name="heading">Security & Activity</x-slot>
                <x-slot name="description">OTP and account timestamps.</x-slot>

                <div class="mt-4 space-y-4">
                    {{-- OTP Status --}}
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">OTP Status</div>
                                <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    @if ($otpHas)
                                        ✅ Has OTP
                                        @if ($otpHuman)
                                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                (expires {{ $otpHuman }})
                                            </span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                            <x-filament::icon icon="heroicon-m-shield-check" class="h-5 w-5 text-gray-400" />
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">OTP Expiry</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $fmt($otpExpiry) }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Created At</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $fmt($u->created_at) }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Updated At</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $fmt($u->updated_at) }}
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- Tip / Notice --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-sm text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-900">
            <div class="flex items-start gap-3">
                <x-filament::icon icon="heroicon-m-information-circle" class="h-5 w-5 text-gray-400 mt-0.5" />
                <div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">Note</div>
                    <div class="mt-1">
                        If profile images don’t display, run
                        <span class="font-mono text-xs px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800">php artisan storage:link</span>
                        and ensure images are stored on the
                        <span class="font-mono text-xs px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800">public</span>
                        disk.
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
