<?php

namespace App\Console\Commands;

use App\Events\ParserErrorEvent;
use App\Parser\ParsersConfig;
use App\Parser\Scheduler;
use App\Parser\Spider\SpiderManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;

class StartCrawl extends Command
{
    protected $signature = 'crawl:start {siteName} {--update}';

    protected $description = 'ParsersConfig start';

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $siteName = $this->argument('siteName');
        $needUpdate = $this->option('update');
        $this->line('Starting parse site:' . $siteName);
        try {
            $parser = new ParsersConfig();
            $siteConfig = $parser->getSiteConfig($siteName);
            $spider = SpiderManager::getSpiderFromConfig($siteConfig);
            $scheduler = new Scheduler($siteConfig);
            $scheduler->setParserLoop($spider);
            if ($needUpdate) {
                $spider->setUpdateMode();
            }
            $spider->crawl();
        } catch (\Exception $e) {
            Event::fire(new ParserErrorEvent(sprintf('%s: Parse error: %s', $siteName, $e->getMessage())));
        }
    }
}
