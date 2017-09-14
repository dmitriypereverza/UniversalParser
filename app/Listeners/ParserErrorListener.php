<?php

namespace App\Listeners;

use App\Events\ParserErrorEvent;
use App\Events\ParserInfoEvent;
use App\Parser\Spider\Log\FileLogger;
use App\Parser\Spider\Log\Logger;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ParserErrorListener
{
    public function __construct()
    {
    }

    public function handle(ParserErrorEvent $event) {
        app('file.logger')->error($event->getText(), $event->getContext());
    }
}
