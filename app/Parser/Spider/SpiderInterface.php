<?php

namespace App\Parser\Spider;

interface SpiderInterface
{
    const DEFAULT_REQUEST_DELAY = 350;
    const DEFAULT_MAX_DEPTH = 4;
    const DEFAULT_QUERY_SIZE = 0;

    public function crawl();

    public function onPostPersistEvent(callable $callback);

    public function setUpdateMode();
}