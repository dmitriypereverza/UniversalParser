<?php

namespace App\Parser;

use App\Parser\Spider\SpiderInterface;
use App\Parser\Spider\SpiderManager;

class Scheduler {
    function __construct() {
    }

    /**
     * Проверка исполнения пауков.
     * Их запуск и остановка
     * @param Parser $parser
     */
    static public function run($parser) {
        foreach ($parser->getArrayConfig() as $site) {
            if (!$site['active']) {
                continue;
            }

            // TODO Проверка времени запуска парсера для каждого сайта

            SpiderManager::getSpiderFromConfig($site)->crawl();
            break;
        }
    }
}