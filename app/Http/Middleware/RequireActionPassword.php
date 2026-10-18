<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deletes and high-risk changes must carry the user's own password in
 * "confirm_password" (asked by the confirmation dialog). Checked on every
 * request, never remembered. Five wrong passwords lock these actions for a minute.
 */
class RequireActionPassword
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;

    public function handle(Request $request, Closure $next): Response
    {
        if ($error = self::verify($request)) {
            return self::reject($request, $error);
        }

        return $next($request);
    }

    /** Returns an error message, or null when the password is correct. */
    public static function verify(Request $request): ?string
    {
        $user = $request->user();
        $key  = 'action-password:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return 'Too many wrong passwords. Try again in ' . RateLimiter::availableIn($key) . ' seconds.';
        }

        if (! Hash::check((string) $request->input('confirm_password'), $user->password)) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            activity('auth')
                ->causedBy($user)
                ->event('password_failed')
                ->withProperties(['action' => $request->method() . ' /' . $request->path(), 'ip' => $request->ip()])
                ->log('Wrong password on a protected action');

            return 'Incorrect password. Nothing was changed.';
        }

        RateLimiter::clear($key);

        return null;
    }

    public static function reject(Request $request, string $error): Response
    {
        if ($request->expectsJson() || $request->isJson()) {
            return response()->json([
                'message' => $error,
                'error'   => $error,
                'errors'  => ['confirm_password' => [$error]],
            ], 422);
        }

        return back()->with('password_error', $error);
    }
}
