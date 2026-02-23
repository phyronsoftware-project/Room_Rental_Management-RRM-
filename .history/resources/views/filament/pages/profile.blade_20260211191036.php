<x-filament-panels::page>

    @php
    

        $u = auth()->user();


        $avatarUrl = null;
        if (! empty($u->profile_image_url)) {
            $avatarUrl = str_starts_with($u->profile_image_url, 'http')
                ? $u->profile_image_url
                : Storage::disk('public')->url($u->profile_image_url);
        }

        // ✅ Role badge style
        $role = strtolower((string) $u->role);
        $roleColor = match ($role) {
            'super_admin' => 'danger',
            'manager'     => 'warning',
            'owner'       => 'success',
            default       => 'gray',
        };

        $propertyName = $u->property?->name ?? null;

        $fmt = fn($dt) => $dt ? $dt->format('d M Y, H:i') : '—';
    @endphp

    {{-- Header Card --}}
    <x-filament::section>
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-4">
                {{-- Avatar --}}
                <div class="shrink-0">
                    @if ($avatarUrl)
                        <img
                            src="{{ $avatarUrl }}"
                            alt="{{ $u->full_name }}"
                            class="h-16 w-16 rounded-full object-cover ring-1 ring-gray-200 dark:ring-gray-700"
                        />
                    @else
                        <div
                            class="h-16 w-16 rounded-full flex items-center justify-center
                                   bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200
                                   ring-1 ring-gray-200 dark:ring-gray-700"
                        >
                            <span class="text-xl font-bold">
                                {{ strtoupper(mb_substr($u->full_name ?? $u->email ?? 'U', 0, 1)) }}
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Name + meta --}}
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-bold tracking-tight truncate">
                            {{ $u->full_name ?? '—' }}
                        </h1>

                        <x-filament::badge :color="$roleColor">
                            {{ $u->role ?? '—' }}
                        </x-filament::badge>
                    </div>

                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        <span class="font-medium">Email:</span>
                        <span class="font-semibold">{{ $u->email ?? '—' }}</span>
                    </div>

                    @if ($propertyName || $u->property_id)
                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                            <span class="font-medium">Property:</span>
                            <span class="font-semibold">
                                {{ $propertyName ?? ('#' . $u->property_id) }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick actions --}}
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
    </x-filament::section>

    {{-- Details Grid --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main Details --}}
        <x-filament::section class="lg:col-span-2">
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-semibold">Account Details</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Basic information about this account.
                    </p>
                </div>

                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">User ID</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $u->user_id ?? '—' }}</dd>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Property ID</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $u->property_id ?? '—' }}</dd>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Full Name</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $u->full_name ?? '—' }}</dd>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $u->email ?? '—' }}</dd>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Role</dt>
                        <dd class="mt-1">
                            <x-filament::badge :color="$roleColor">{{ $u->role ?? '—' }}</x-filament::badge>
                        </dd>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Password</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{-- Don't show hash --}}
                            ✅ Set
                        </dd>
                    </div>
                </dl>
            </div>
        </x-filament::section>

        {{-- Security / OTP / Timestamps --}}
        <x-filament::section>
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-semibold">Security & Activity</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        OTP and account timestamps.
                    </p>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <div>
                            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">OTP Code</div>
                            <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ $u->otp_code ? '✅ Has OTP' : '—' }}
                            </div>
                        </div>
                        <x-filament::icon icon="heroicon-m-shield-check" class="h-5 w-5 text-gray-400" />
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">OTP Expiry</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $fmt($u->otp_expiry) }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Created At</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $fmt($u->created_at) }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Updated At</div>
                        <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $fmt($u->updated_at) }}
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>

    {{-- Small tip --}}
    <div class="mt-6 text-xs text-gray-500 dark:text-gray-400">
        Note: If profile images don’t display, run <span class="font-mono">php artisan storage:link</span> and ensure images are stored on the <span class="font-mono">public</span> disk.
    </div>
</x-filament-panels::page>
