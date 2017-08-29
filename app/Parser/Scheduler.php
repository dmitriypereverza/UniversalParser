<?php

namespace App\Parser;

use App\Parser\Spider\SpiderManager;
use Carbon\Carbon;

class Scheduler {
    private static $dayOfWeek = ['Monday', 'Tuesday', 'Wednesday','Thursday','Friday', 'Saturday', 'Sunday'];
    private $parserWorks = false;
    function __construct() {
    }

    /**
     * Проверка исполнения пауков.
     * Их запуск и остановка
     * @param Parser $parser
     */
    public function run($parser) {
        foreach ($parser->getArrayConfig() as $site) {
            if (!$site['active']) {
                continue;
            }
            $time = Carbon::now('Europe/Moscow');
            $dayOfWeek = $time->dayOfWeek;
            $workTime = $site['work_time'][self::$dayOfWeek[$dayOfWeek - 1]];

            foreach ($workTime as $timeDiff) {
                /** @var string $time_from */
                /** @var string $time_to */
                extract($timeDiff);
                $timeFromParts = explode(':', $time_from);
                $timeToParts = explode(':', $time_to);

                $isMatch = $time->between(
                    Carbon::today('Europe/Moscow')->addHours($timeFromParts[0])->addMinutes($timeFromParts[1]),
                    Carbon::today('Europe/Moscow')->addHours($timeToParts[0])->addMinutes($timeToParts[1])
                );

                if ($isMatch) {
                    $this->parserWorks = true;
                    SpiderManager::getSpiderFromConfig($site)->crawl();
                    break;
                }
            }
        }
    }
}