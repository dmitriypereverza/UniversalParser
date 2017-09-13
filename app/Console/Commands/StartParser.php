<?php
namespace App\Console\Commands;

use App\Events\ParserErrorEvent;
use App\Parser\ParsersConfig;
use App\Parser\Scheduler;
use App\Parser\Spider\SpiderManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;

class StartParser extends Command {
    protected $signature = 'parser:start {siteName}';

    protected $description = 'ParsersConfig start';

    function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $siteName = $userId = $this->argument('siteName');
        $this->line('Starting parse site:' . $siteName);
        try {
            $parser = new ParsersConfig();
            $siteConfig = $parser->getSiteConfig($siteName);
            $spider = SpiderManager::getSpiderFromConfig($siteConfig);
            $scheduler = new Scheduler($siteConfig);
            $scheduler->setParserLoop($spider);

            $spider->crawl();
        } catch (\Exception $e) {
            Event::fire(new ParserErrorEvent(
                sprintf('Parse error: %s', $e->getMessage())
            ));
        }
    }
}
