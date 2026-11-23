<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\StaffPanelProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    StaffPanelProvider::class,
];
