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
        if ($this->parserWorks) {
            return;
        }

        foreach ($parser->getArrayConfig() as $site) {
            if (!$site['active']) {
                continue;
            }
            $nowTime = Carbon::now('Europe/Moscow');
            foreach ($this->getWorkTimeForCurrentDay($site, $nowTime->dayOfWeek) as $timeDiff) {
                if ($this->isMatch($nowTime, $timeDiff)) {
                    $this->parserWorks = true;
                    SpiderManager::getSpiderFromConfig($site)->crawl();
                    break;
                }
            }
        }
    }

    /**
     * @param Carbon $time
     * @param $timeDiff
     * @return bool
     */
    private function isMatch($time, $timeDiff) {
        extract($timeDiff);
        /** @var string $time_from */
        $timeFromParts = explode(':', $time_from);
        /** @var string $time_to */
        $timeToParts = explode(':', $time_to);
        return $time->between(Carbon::today('Europe/Moscow')->addHours($timeFromParts[0])->addMinutes($timeFromParts[1]), Carbon::today('Europe/Moscow')->addHours($timeToParts[0])->addMinutes($timeToParts[1]));
    }

    /**
     * @param $siteConfig
     * @param $currentDayOfWeek
     * @return array
     */
    private function getWorkTimeForCurrentDay($siteConfig, $currentDayOfWeek) {
        return $siteConfig['work_time'][self::$dayOfWeek[$currentDayOfWeek - 1]];
    }
}