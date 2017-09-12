<?php
namespace App\Parser;

use App\Parser\Spider\SpiderInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Symfony\Component\EventDispatcher\Event;

class Scheduler {
    private static $timeZone;
    private $siteConfig;
    private static $dayOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    /** @var SpiderInterface $spider */

    public function __construct($siteConfig) {
        self::$timeZone = Config::get('app.timezone');
        $this->siteConfig = $siteConfig;
    }

    public function setParserLoop(SpiderInterface $parser) {
        echo "Check\n";
        $parser->onPostPersistEvent(function (Event $event) {
            if (!$this->isMustWork($this->siteConfig)) {
                $event->getSubject()->setDownloadLimit(1);
            }
        });
    }

    /**
     * @param $siteConfig
     * @return bool
     */
     private function isMustWork($siteConfig) {
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
        return $siteConfig['work_time'][self::$dayOfWeek[$currentDayOfWeek - 1] . 's'];
    }

}