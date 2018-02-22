<?php

namespace App\Listeners;

use App\Events\ParserErrorEvent;
use App\Events\ParserTreeMakerEvent;
use App\Events\SparePartParserEvent;

class SparePartParserListener
{
    public function handle(SparePartParserEvent $event)
    {
        app('console.logger')->info(
            $event->getText(),
            $event->getContext()
        );

        app('file.spare_parts.logger')
            ->info(
                $event->getText(),
                $event->getContext()
            );
    }
}
