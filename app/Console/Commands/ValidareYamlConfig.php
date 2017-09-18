<?php

namespace App\Console\Commands;

use App\Parser\ParsersConfig;
use App\Parser\Scheduler;
use Illuminate\Console\Command;

class ValidareYamlConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'config:validate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        $parser = new ParsersConfig(ParsersConfig::SCHEDULE_CONFIG);

        if ($error = $parser->getError()) {
            $this->error(sprintf('Config file has error: %s', $error));
            return;
        }
        foreach ($parser->getArrayConfig() as $siteKey => $site) {
            $dayCounter = 0;
            foreach ($site as $dayOfWeek => $values) {
                if ($dayOfWeek != Scheduler::$dayOfWeek[$dayCounter] . 's') {
                    $this->error(sprintf('%s must be %s', $dayOfWeek, Scheduler::$dayOfWeek[$dayCounter] . 's'));
                    return;
                }
                $dayCounter++;

                if (!is_array($values)) {
                    continue;
                }
                foreach ($values as $rowTime) {
                    if (array_key_exists('time_from', $rowTime)) {
                        if (!preg_match("/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/", $rowTime['time_from'])) {
                            $this->error(sprintf('In %s > %s. time_from must be a valid time', $siteKey, $dayOfWeek));
                            return;
                        }
                    } else {
                        $this->error(sprintf('In %s > %s. Does\'t have time_from', $siteKey, $dayOfWeek));
                        return;
                    }
                    if (array_key_exists('time_to', $rowTime)) {
                        if (!preg_match("/^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/", $rowTime['time_to'])) {
                            $this->error(sprintf('In %s > %s. time_to must be a valid time', $siteKey, $dayOfWeek));
                            return;
                        }
                    } else {
                        $this->error(sprintf('In %s > %s. Does\'t have time_to', $siteKey, $dayOfWeek));
                        return;
                    }
                }
            }
        }

        $this->info(sprintf('Config file is valid'));

    }
}
