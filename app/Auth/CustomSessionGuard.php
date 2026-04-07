<?php

namespace App\Auth;

use Illuminate\Auth\SessionGuard;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Overrides the remember-me cookie lifetime to 1 day.
 * Laravel's default SessionGuard uses forever() (~5 years).
 */
class CustomSessionGuard extends SessionGuard
{
    protected function createRecaller($value): Cookie
    {
        return $this->getCookieJar()->make(
            $this->getRecallerName(),
            $value,
            60 * 24  // 1 day in minutes
        );
    }
}
