<?php

namespace App\Prototype;

class PrototypeManager
{
    /**
     * Determine if prototype mode is allowed and active.
     */
    public static function isEnabled(): bool
    {
        // Absolute guard: never allow in production under any circumstance
        if (app()->environment('production')) {
            return false;
        }

        $config = config('prototype.enabled');
        if ($config !== null) {
            return (bool) $config;
        }

        return app()->environment('local', 'testing');
    }
}
