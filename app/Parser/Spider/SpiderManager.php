<?php

namespace App\Parser\Spider;

class SpiderManager {
    /**
     * @param array $spiderConfig
     * @return SpiderInterface
     */
    static public function getSpiderFromConfig($spiderConfig) {
        if (!class_exists($spiderConfig['class'])) {
            return null;
        }

        return new $spiderConfig['class']($spiderConfig);
    }
}