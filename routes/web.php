<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Livewire\Listings\Index as ListingsIndex;
use App\Livewire\Listings\Create as ListingsCreate;
use App\Livewire\Listings\Manage as ListingsManage;
use App\Livewire\Settings\Skills as SettingsSkills;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth','verified'])->prefix('dashboard')->group(function () {
    Route::get('/', ListingsIndex::class)->name('dashboard');
    Route::get('/listings/create', ListingsCreate::class)->name('listings.create');
    Route::get('/listings/mine', ListingsManage::class)->name('listings.mine');
    Route::get('/settings/skills', SettingsSkills::class)->name('settings.skills');

});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
