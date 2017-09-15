<?php

namespace App\Listeners;

use App\Events\ParserInfoEvent;

class ParserInfoListener
{
    public function handle(ParserInfoEvent $event)
    {
        app('console.logger')->info($event->getText(), $event->getContext());
        app('file.logger')->info($event->getText(), $event->getContext());
    }
}
