<?php

namespace App\Parser\Spider;

interface SpiderInterface {
    public function crawl();
    public function onPersistEvent(callable $callback);
    public function getConfig();
}