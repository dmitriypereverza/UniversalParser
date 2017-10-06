<?php

namespace App\Console\Commands;

use App\Parser\Spider\TreeLinksSpider;
use Illuminate\Console\Command;
use Symfony\Component\EventDispatcher\Event;

class ParseTree extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:tree {site}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
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
        $site = $this->argument('site');
        if (!$site) {
            $this->error('Site not found!');
            return;
        }

        $parser = new TreeLinksSpider($site);
        $parser->crawl();
        $parser->onPostPersistEvent(function (Event $event) {
            if (!ParserStatus::isEnable() || !$this->isMustWork($this->siteConfig)) {
                $event->getSubject()->setDownloadLimit(1);
            }
        });
    }
}
