<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\ParserInfoEvent' => [
            'App\Listeners\ParserInfoListener',
        ],
        'App\Events\ParserErrorEvent' => [
            'App\Listeners\ParserErrorListener',
        ],
        'App\Events\ParserTreeMakerEvent' => [
            'App\Listeners\ParserTreeMakerListener',
        ],
        'App\Events\SparePartParserEvent' => [
            'App\Listeners\SparePartParserListener',
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
