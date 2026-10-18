<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Never run tests against the real database: RefreshDatabase would wipe it.
        $connection = $app['config']->get('database.default');
        $database   = (string) $app['config']->get("database.connections.{$connection}.database");

        if (! str_ends_with($database, '_test')) {
            throw new \RuntimeException(
                "Refusing to run tests on database \"{$database}\". Tests must use a database whose name ends with _test "
                . '(set in phpunit.xml). If the name is right there, run: php artisan config:clear'
            );
        }

        return $app;
    }
}
