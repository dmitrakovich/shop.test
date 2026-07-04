<?php

namespace App\Exceptions;

use App\Facades\Device;
use Filament\Facades\Filament;
use Throwable;

final class ReportDeviceError
{
    public function __invoke(Throwable $exception): void
    {
        if (!$this->shouldReport()) {
            return;
        }

        Device::current()->registerError($exception);
    }

    private function shouldReport(): bool
    {
        if ($this->isConsoleProcess()) {
            return false;
        }

        if ($this->isAdminRequest()) {
            return false;
        }

        if (!Device::isResolved()) {
            return false;
        }

        return Device::current()->exists;
    }

    private function isConsoleProcess(): bool
    {
        return app()->runningInConsole() && !app()->runningUnitTests();
    }

    private function isAdminRequest(): bool
    {
        if (!app()->has('request')) {
            return false;
        }

        $path = request()->path();

        $filamentPath = $this->filamentPanelPath();

        if (str_starts_with($path, $filamentPath . '/') || $path === $filamentPath) {
            return true;
        }

        $legacyPrefix = config('admin.route.prefix');

        if (str_starts_with($path, $legacyPrefix . '/') || $path === $legacyPrefix) {
            return true;
        }

        return str_starts_with($path, 'livewire-');
    }

    private function filamentPanelPath(): string
    {
        static $path = null;

        if ($path !== null) {
            return $path;
        }

        try {
            $path = Filament::getPanel('admin')->getPath();
        } catch (Throwable) {
            $path = 'admin';
        }

        return $path;
    }
}
