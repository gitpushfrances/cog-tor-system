<?php

namespace App\Exceptions;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // A second save of the same record (double click, two tabs) hits a unique key:
        // tell the user instead of showing a server error. Nothing is saved twice.
        $this->renderable(function (UniqueConstraintViolationException $e, $request) {
            $message = 'This was already saved (for example by a double click or another tab). Nothing was saved twice. Refresh the page to see the current data.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message, 'error' => $message], 409);
            }

            return back()->withInput($request->except($this->dontFlash))->with('error', $message);
        });
    }
}
