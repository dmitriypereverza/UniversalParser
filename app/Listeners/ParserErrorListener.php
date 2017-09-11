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

    public function handle(ParserInfoEvent $event) {
        /** @var Logger $logger */
        $logger = app(Logger::class);
        $logger->info($event->getText(), $event->getContext());
        $logger->setLogger(new FileLogger());
    }
}
