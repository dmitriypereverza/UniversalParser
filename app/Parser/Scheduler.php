<?php

namespace App\Parser;

use App\Parser\Spider\SpiderInterface;
use App\Parser\Spider\SpiderManager;
use Carbon\Carbon;
use Symfony\Component\EventDispatcher\Event;

class Scheduler {
    private static $_instance = null;
    private static $dayOfWeek = ['Monday', 'Tuesday', 'Wednesday','Thursday','Friday', 'Saturday', 'Sunday'];
    /** @var SpiderInterface $parserInWork */
    private $parserInWork = false;

    private function __construct() {
    }

    static public function getInstance() {
        if(is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Проверка исполнения пауков.
     * @param Parser $parser
     */
    public function run($parser) {
        if($this->isParserWorks()) {
            return;
        };
        foreach ($parser->getArrayConfig() as $site) {
            if (!$site['active']) {
                continue;
            }
            $nowTime = Carbon::now('Europe/Moscow');
            foreach ($this->getWorkTimeForCurrentDay($site, $nowTime->dayOfWeek) as $timeDiff) {
                if ($this->isMatch($nowTime, $timeDiff)) {
                    $this->parserInWork = SpiderManager::getSpiderFromConfig($site);
                    $this->setParserLoop($this->parserInWork);
                    $this->parserInWork->crawl();
                    $this->parserInWork = Null;
                }
            }
        }
    }

    private function setParserLoop(SpiderInterface $parser) {
        $parser->onPersistEvent(function (Event $event) {
            if ($this->isNeedStop($this->parserInWork->getConfig())) {
                $event->getSubject()->getDownloader()->setDownloadLimit(1);
                // TODO Log
                echo "Parser time expired!!!!!\n";
            }
        });
    }

    /**
     * @param $siteConfig
     * @return bool
     */
    function isNeedStop($siteConfig) {
        $needStop = true;
        $nowTime = Carbon::now('Europe/Moscow');
        foreach ($this->getWorkTimeForCurrentDay($siteConfig, $nowTime->dayOfWeek) as $timeDiff) {
            if ($this->isMatch($nowTime, $timeDiff)) {
                $needStop = false;
                break;
            }
        }
        return $needStop;
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

    public function isParserWorks() {
        return is_null($this->parserInWork);
    }
}