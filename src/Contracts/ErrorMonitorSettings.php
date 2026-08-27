<?php

declare(strict_types=1);

namespace HeritageApps\ErrorMonitor\Contracts;

/**
 * Apps may bind their own implementation in a service provider to make
 * recipients/threshold editable at runtime (e.g. from a landlord settings
 * screen backed by a LandlordSetting model):
 *
 *   $this->app->bind(ErrorMonitorSettings::class, MyLandlordSettingsAdapter::class);
 */
interface ErrorMonitorSettings
{
    /**
     * @return array<int, string>
     */
    public function recipients(): array;

    public function repeatThresholdMinutes(): int;
}
