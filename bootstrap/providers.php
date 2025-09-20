<?php

return [
    // Provider inti aplikasi
    App\Providers\AppServiceProvider::class,
	 App\Providers\AuthServiceProvider::class,

    // Provider panel Filament (hasil make:filament-panel)
    App\Providers\Filament\AdminPanelProvider::class,
];
