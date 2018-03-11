<?php

namespace App\Console\Commands;

use App\Models\EuroAutoLinks;
use App\Models\RefModels;
use App\Parser\ParsersConfig;
use App\Parser\Spider\SpiderManager;
use Illuminate\Console\Command;

class GetSparePartByUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:spareParts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get spare parts by EuroAuto links.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $parser = new ParsersConfig();
        $siteConfig = $parser->getSiteConfig("euro_auto_spare_parts");

        while ($link = EuroAutoLinks::whereNull('is_recived')->limit(20)->get()->random(1)->first()) {
            $siteConfig['items_list_url'] = $this->normalizeUrl($link->root_model_link);

            $this->info(sprintf("Start crawl url: %s", $siteConfig['items_list_url']));
            $spider = SpiderManager::getSpiderFromConfig($siteConfig);
            $spider->crawl();
            $this->info(sprintf("Crawl ended url: %s", $siteConfig['items_list_url']));
            $link->is_recived = true;
            $link->save();
        }

        $this->line("All spare parts from EuroAuto links is received");
    }

    private function normalizeUrl($link)
    {
        $urlInfo = parse_url($link);
        if (!array_key_exists("scheme", $urlInfo) || empty($urlInfo["scheme"])) {
            $link = "http://" . $link;
        }
        return $link;
    }
}
