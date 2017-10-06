<?php

namespace App\Listeners;

use App\Events\ParserErrorEvent;
use App\Events\ParserTreeMakerEvent;

class ParserTreeMakerListener
{
    public function handle(ParserTreeMakerEvent $event)
    {
        app('console.logger')->info($event->getText(), $event->getContext());
    }
}
