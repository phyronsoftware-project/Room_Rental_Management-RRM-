<x-filament-panels::page>
    @php
        $u = auth()->user();

        // current avatar url from DB
        $avatarUrl = null;
        if (! empty($u->profile_image_url)) {
            $avatarUrl = str_starts_with($u->profile_image_url, 'http')
                ? $u->profile_image_url
                : \Illuminate\Support\Facades\Storage::disk('public')->url($u->profile_image_url);
        }

        // Livewire temp preview (if user selected new file)
        $previewAvatar = isset($this) && ! empty($this->avatar)
            ? $this->avatar->temporaryUrl()
            : $avatarUrl;

        $initial = strtoupper(mb_substr($u->full_name ?: ($u->email ?: 'U'), 0, 1));
    @endphp

    <form wire:submit.prevent="save" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- LEFT: Profile Card --}}
        <div class="lg:col-span-1">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6">
                <div class="text-center space-y-4">
                    <div class="text-xl font-semibold text-gray-800 dark:text-gray-100">Profile</div>

                    {{-- Avatar --}}
                    <div class="mx-auto w-40 h-40 rounded-full overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                        @if ($previewAvatar)
                            <img src="{{ $previewAvatar }}" class="w-full h-full object-cover" alt="avatar">
                        @else
                            <span class="text-5xl font-bold text-gray-600 dark:text-gray-200">{{ $initial }}</span>
                        @endif
                    </div>

                    {{-- Name --}}
                    <div class="text-gray-700 dark:text-gray-200 font-medium">
                        {{ $u->full_name ?? '—' }}
                    </div>

                    {{-- Change Avatar --}}
                    <div class="space-y-2">
                        <input id="avatar" type="file" wire:model="avatar" class="hidden" />

                        <label for="avatar"
                               class="inline-flex items-center justify-center px-4 py-2 rounded-lg
                                      bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold
                                      cursor-pointer transition">
                            Change Avatar
                        </label>

                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            @if(!empty($this->avatar))
                                {{ $this->avatar->getClientOriginalName() }}
                            @else
                                No file chosen
                            @endif
                        </div>

                        @error('avatar')
                            <div class="text-xs text-danger-600">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT: Edit Details + Change Password --}}
        <div class="lg:col-span-2">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6">
                {{-- Edit Details --}}
                <div class="space-y-5">
                    <div class="text-xl font-semibold text-gray-800 dark:text-gray-100">Edit Details</div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">First Name</label>
                            <input type="text" wire:model.defer="first_name"
                                   class="w-full rounded-lg border border-gray-200 dark:border-gray-700
                                          bg-white dark:bg-gray-900 px-3 py-2 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @error('first_name') <div class="mt-1 text-xs text-danger-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Last Name</label>
                            <input type="text" wire:model.defer="last_name"
                                   class="w-full rounded-lg border border-gray-200 dark:border-gray-700
                                          bg-white dark:bg-gray-900 px-3 py-2 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-primary-500">
                            @error('last_name') <div class="mt-1 text-xs text-danger-600">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Email Address</label>
                        <input type="email" wire:model.defer="email"
                               class="w-full rounded-lg border border-gray-200 dark:border-gray-700
                                      bg-white dark:bg-gray-900 px-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @error('email') <div class="mt-1 text-xs text-danger-600">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Divider --}}
                <div class="my-8 border-t border-gray-200 dark:border-gray-800"></div>

                {{-- Change Password --}}
                <div class="space-y-5">
                    <div class="text-xl font-semibold text-gray-800 dark:text-gray-100">Change Password</div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                            Password <span class="text-xs text-gray-400">(Leave blank to keep current password)</span>
                        </label>
                        <input type="password" wire:model.defer="password"
                               class="w-full rounded-lg border border-gray-200 dark:border-gray-700
                                      bg-white dark:bg-gray-900 px-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @error('password') <div class="mt-1 text-xs text-danger-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                            Confirm Password <span class="text-xs text-gray-400">(Leave blank to keep current password)</span>
                        </label>
                        <input type="password" wire:model.defer="password_confirmation"
                               class="w-full rounded-lg border border-gray-200 dark:border-gray-700
                                      bg-white dark:bg-gray-900 px-3 py-2 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @error('password_confirmation') <div class="mt-1 text-xs text-danger-600">{{ $message }}</div> @enderror
                    </div>

                    {{-- Save button (left like screenshot) --}}
                    <div class="pt-2">
                        <x-filament::button type="submit" icon="heroicon-m-check">
                            Save
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-filament-panels::page>
