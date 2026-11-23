<?php

namespace App\Services\Applications;

use App\Models\Application;
use App\Models\Property;
use Illuminate\Support\Collection;

/**
 * Warns when the same plot and block already has an open application.
 * It is a warning, not a block: the person registering can acknowledge it
 * (e.g. for an amendment), and the acknowledgement is stored on the application.
 */
class DuplicateChecker
{
    /** @return Collection<int, Application> */
    public function openApplicationsFor(?string $plotNumber, ?string $blockNumber): Collection
    {
        $plotKey = Property::normaliseKey($plotNumber);
        $blockKey = Property::normaliseKey($blockNumber);

        if ($plotKey === '' || $blockKey === '') {
            return collect();
        }

        return Application::query()
            ->open()
            ->whereHas('property', fn ($q) => $q->where('plot_key', $plotKey)->where('block_key', $blockKey))
            ->orderBy('application_date')
            ->get(['id', 'application_number', 'current_status', 'application_date']);
    }
}
