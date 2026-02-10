<x-filament-panels::page>
    @php($u = auth()->user())

    <x-filament::section>
        <div class="flex items-center gap-4">
            @if($u->profile_image_url)
                <img src="{{ $u->profile_image_url }}" class="h-16 w-16 rounded-full object-cover ring-1 ring-gray-200 dark:ring-gray-700" />
            @else
                <div class="h-16 w-16 rounded-full flex items-center justify-center bg-gray-100 dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700">
                    {{ strtoupper(mb_substr($u->full_name ?? 'A', 0, 1)) }}
                </div>
            @endif

            <div class="min-w-0">
                <div class="text-2xl font-bold truncate">{{ $u->full_name }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Email: <span class="font-semibold">{{ $u->email }}</span>
                    • Role: <span class="font-semibold">{{ $u->role }}</span>
                </div>

                @if ($u->property)
                    <div class="text-sm text-gray-600 dark:text-gray-300">
                        Property: <span class="font-semibold">{{ $u->property->name ?? ('#'.$u->property_id) }}</span>
                    </div>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
