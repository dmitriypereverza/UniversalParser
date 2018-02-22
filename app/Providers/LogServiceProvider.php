<?php

namespace App\Providers;

use App\Parser\Spider\Log\FileLogger;
use App\Parser\Spider\Log\Logger;
use Illuminate\Support\ServiceProvider;

class LogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('console.logger', function ($app) {
            return new Logger();
        });

        $this->app->bind('file.logger', function ($app) {
            return new Logger(new FileLogger(getenv('PARSER_LOG_FILE')));
        });

        $this->app->bind('file.spare_parts.logger', function ($app) {
            return new Logger(new FileLogger(getenv('SPARE_PART_PARSER_LOG_FILE')));
        });
    }
}