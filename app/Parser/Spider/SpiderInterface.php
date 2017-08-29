<?php

namespace App\Parser\Spider;

interface SpiderInterface {
    public function crawl();
    public function stop();
}