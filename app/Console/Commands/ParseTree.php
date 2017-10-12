<?php

namespace App\Console\Commands;

use App\Parser\Spider\TreeLinksSpider;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ParseTree extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:tree {site} {depth} {request_delay}';

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
        $depth = $this->argument('depth');
        $requestDelay = $this->argument('request_delay');
        if (!$site) {
            $this->error('Site not found!');
            return;
        }

        $parser = new TreeLinksSpider($site, $depth, $requestDelay);
        $parser->crawl();
    }
}
