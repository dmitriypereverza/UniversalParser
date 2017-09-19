<?php

namespace App\Console\Commands;

use App\Models\TemporarySearchResults;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\ParserStatus as Parser;
use Illuminate\Support\Facades\Config;

class ParserStatistic extends Command
{
    protected $signature = 'parser:statistic {hour}';

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
        $hour = $this->argument('hour');
        if (!filter_var($hour, FILTER_VALIDATE_INT) || $hour <= 0) {
            $this->error('Argument must be a number and >= 0.');
            return;
        }
        $now = Carbon::now(Config::get('app.timezone'));
        $diff = Carbon::now(Config::get('app.timezone'))->subHour($hour);
        $rows = TemporarySearchResults::selectRaw(\DB::raw('config_site_name, count(id) as count_url'))
            ->whereBetween('created_at', [$diff, $now])
            ->groupBy('config_site_name')
            ->get();

        if ($rows->count()) {
            $this->info(sprintf("Number of items received from %s to %s:", $diff, $now));
            foreach ($rows as $row) {
                $this->info(sprintf('Site url: %s Unique elements: %s', $row->config_site_name, $row->count_url));
            }
        } else {
            $this->line(sprintf("Did not receive any items from %s to %s:", $diff, $now));
        }
    }
}