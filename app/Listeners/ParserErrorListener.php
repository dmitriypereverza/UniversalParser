<?php
namespace App\Listeners;

use App\Events\ParserErrorEvent;

class ParserErrorListener {
    public function handle(ParserErrorEvent $event) {
        app('file.logger')->error($event->getText(), $event->getContext());
    }
}
