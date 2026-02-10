@php
    $user = filament()->auth()->user();
    $items = filament()->getUserMenuItems();

    $profileItem = $items['profile'] ?? ($items['account'] ?? null);
    $profileItemUrl = $profileItem?->getUrl();
    $profilePage = filament()->getProfilePage();
    $hasProfileItem = filament()->hasProfile() || filled($profileItemUrl);

    $logoutItem = $items['logout'] ?? null;

    $items = \Illuminate\Support\Arr::except($items, ['account', 'logout', 'profile']);
@endphp

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_BEFORE) }}

<x-filament::dropdown placement="bottom-end" teleport :attributes="\Filament\Support\prepare_inherited_attributes($attributes)->class(['fi-user-menu'])">
    <x-slot name="trigger">
        <button aria-label="{{ __('filament-panels::layout.actions.open_user_menu.label') }}" type="button"
            class="shrink-0">
            <x-filament-panels::avatar.user :user="$user" />
        </button>
    </x-slot>

    @if ($profileItem?->isVisible() ?? true)
        <x-filament::dropdown.list>
            <x-filament::dropdown.list.item :icon="\Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.profile-item') ??
                'heroicon-m-user-circle'" :href="\App\Filament\Pages\Profile::getUrl()" tag="a">
                <div class="flex flex-col leading-tight">
                    <span class="font-medium">
                        {{ filament()->getUserName($user) }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $user->email }}
                    </span>
                </div>
            </x-filament::dropdown.list.item>
        </x-filament::dropdown.list>
    @endif


    @if (filament()->hasDarkMode() && !filament()->hasDarkModeForced())
        <x-filament::dropdown.list>
            <x-filament-panels::theme-switcher />
        </x-filament::dropdown.list>
    @endif

    <x-filament::dropdown.list>
        @foreach ($items as $key => $item)
            @php
                $itemPostAction = $item->getPostAction();
            @endphp

            <x-filament::dropdown.list.item :action="$itemPostAction" :color="$item->getColor()" :href="$item->getUrl()" :icon="$item->getIcon()"
                :method="filled($itemPostAction) ? 'post' : null" :tag="filled($itemPostAction) ? 'form' : 'a'" :target="$item->shouldOpenUrlInNewTab() ? '_blank' : null">
                {{ $item->getLabel() }}
            </x-filament::dropdown.list.item>
        @endforeach

        <x-filament::dropdown.list.item :action="$logoutItem?->getUrl() ?? filament()->getLogoutUrl()" :color="$logoutItem?->getColor()" :icon="$logoutItem?->getIcon() ??
            (\Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.logout-button') ??
                'heroicon-m-arrow-left-on-rectangle')" method="post"
            tag="form">
            {{ $logoutItem?->getLabel() ?? __('filament-panels::layout.actions.logout.label') }}
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>
</x-filament::dropdown>

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_AFTER) }}
