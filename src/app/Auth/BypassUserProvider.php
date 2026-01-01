<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

/**
 * A small user provider that allows bypassing password checks for a specific
 * admin email when explicitly enabled via env var `ADMIN_LOGIN_BYPASS=true`.
 * This is intended for local/dev convenience only and should NOT be enabled
 * in production. It falls back to normal Eloquent behaviour for other users.
 */
class BypassUserProvider extends EloquentUserProvider
{
    public function __construct(HasherContract $hasher, $model)
    {
        parent::__construct($hasher, $model);
    }

    public function validateCredentials($user, array $credentials)
    {
        // Only allow bypass when explicitly enabled and for the configured admin email
        $bypassEnabled = (bool) env('ADMIN_LOGIN_BYPASS', false);
        $bypassEmail = env('ADMIN_BYPASS_EMAIL', 'admin@admin.com');

        if ($bypassEnabled && isset($credentials['email']) && $credentials['email'] === $bypassEmail) {
            return true;
        }

        // Otherwise fall back to default behaviour
        return parent::validateCredentials($user, $credentials);
    }
}
