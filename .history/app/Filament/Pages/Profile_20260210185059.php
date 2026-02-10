<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Profile extends Page
{
    protected static ?string $slug = 'profile'; // ✅ /admin/profile
    protected static ?string $title = 'My Profile';
    protected static bool $shouldRegisterNavigation = false; // ✅ មិនបង្ហាញនៅ sidebar
    protected static string $view = 'filament.pages.profile';
}
