<?php

namespace App\Parser;

use App\Parser\Spider\SpiderInterface;
use App\Parser\Spider\SpiderManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Symfony\Component\EventDispatcher\Event;

class Scheduler {
    private static $_instance = null;
    private static $timeZone;
    private static $dayOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    /** @var SpiderInterface $parserInWork */
    private $parserInWork = false;

    private function __construct() {
        self::$timeZone = Config::get('app.timezone');
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
        foreach ($parser->getArrayConfig() as $site) {
            if ($this->isMustWork($site)) {
                $this->parserInWork = SpiderManager::getSpiderFromConfig($site);
                $this->setParserLoop($this->parserInWork);
                $this->parserInWork->crawl();
                $this->parserInWork = Null;
            }
        }
    }

    private function setParserLoop(SpiderInterface $parser) {
        $parser->onPostPersistEvent(function (Event $event) {
            if (!$this->isMustWork($this->parserInWork->getConfig())) {
                $event->getSubject()->setDownloadLimit(1);
            }
        });
    }

    /**
     * @param $siteConfig
     * @return bool
     */
    function isMustWork($siteConfig) {
        $mustWork = false;
        if (!$siteConfig['active']) {
            return false;
        }
        $nowTime = Carbon::now(self::$timeZone);
        foreach ($this->getWorkTimeForCurrentDay($siteConfig, $nowTime->dayOfWeek) as $timeDiff) {
            if ($this->isMatch($nowTime, $timeDiff)) {
                $mustWork = true;
                break;
            }
        }
        return $mustWork;
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
        return $time->between(Carbon::today(self::$timeZone)->addHours($timeFromParts[0])->addMinutes($timeFromParts[1]), Carbon::today(self::$timeZone)->addHours($timeToParts[0])->addMinutes($timeToParts[1]));
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