<?php

namespace App\Jobs;

use App\Parser\Spider\TreeLinksSpider;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ParseTree implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var
     */
    private $site;
    /**
     * @var
     */
    private $depth;
    /**
     * @var
     */
    private $requestDelay;

    public function __construct($site, $depth, $requestDelay)
    {
        if (!$site) {
            throw new \Exception('Site not found');
        }
        $this->site = $site;
        $this->depth = $depth;
        $this->requestDelay = $requestDelay;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $parser = new TreeLinksSpider($this->site, $this->depth, $this->requestDelay);
        $parser->crawl();
    }
}