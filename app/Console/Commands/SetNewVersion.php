<?php

namespace App\Console\Commands;

use App\Events\ParserErrorEvent;
use App\Models\TemporarySearchResults;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event as laravelEvent;

class SetNewVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'versions:set';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set new version each hour';

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
        while ($nullVersionElement = TemporarySearchResults::where('version', 0)->first()) {
            $elementCount = TemporarySearchResults::where(['version' => 0, 'id_session' => $nullVersionElement->id_session])->count();
            try {
                $newVersion = app('version.manager')->getNewVersion($elementCount);
                TemporarySearchResults::setVersion($nullVersionElement->id_session, $newVersion);
            } catch (\Exception $e) {
                laravelEvent::fire(new ParserErrorEvent(sprintf("%s: Error while getting new version: %s in %s line %s", $nullVersionElement->config_site_name, $e->getMessage(), $e->getFile(), $e->getLine())));
            }
        }
    }
}
