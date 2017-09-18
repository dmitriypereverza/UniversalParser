<?php

namespace App\Console;

use App\Console\Commands\getConfig;
use App\Console\Commands\ParserStatistic;
use App\Console\Commands\StartCrawl;
use App\Console\Commands\UpdateProxy;
use App\Console\Commands\ParserStatus;
use App\Models\ParserStatus as Parser;
use App\Parser\ParsersConfig;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Config;
use Psr\Log\InvalidArgumentException;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        GetConfig::class,
        StartCrawl::class,
        UpdateProxy::class,
        ParserStatus::class,
        ParserStatistic::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (Parser::isEnable()) {
            $this->setParserTask($schedule);
        }
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }

    /**
     * @param Schedule $schedule
     */
    protected function setParserTask(Schedule $schedule)
    {
        $parser = new ParsersConfig();
        $daysOfWeek = ['Mondays', 'Tuesdays', 'Wednesdays', 'Thursdays', 'Fridays', 'Saturdays', 'Sundays'];
        foreach ($parser->getArrayConfig() as $siteName => $siteConfig) {
            if (!$siteConfig['active']) {
                continue;
            }
            foreach ($siteConfig['work_time'] as $dayOfWeek => $times) {
                if (!in_array($dayOfWeek, $daysOfWeek)) {
                    throw new InvalidArgumentException('Invalid name for day of week:' + $dayOfWeek);
                }

                foreach ($times as $timeCase) {
                    $schedule->command('crawl:start', [$siteName])
                        ->{strtolower($dayOfWeek)}()
                        ->everyMinute()
                        ->timezone(Config::get('app.timezone'))
                        ->between($timeCase['time_from'], $timeCase['time_to'])
                        ->name('parserTask')
                        ->withoutOverlapping();
                }
            }
        }
    }
}
