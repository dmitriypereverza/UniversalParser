<?php
namespace App\Parser\Log;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Psr\Log\AbstractLogger;

class ConsoleLogger extends AbstractLogger {

    public function log($level, $message, array $context = array()) {
        echo sprintf(
            "[%s] %s : %s\nContext: \n%s",
                Carbon::now(Config::get('app.timezone')),
                $level,
                $message,
                implode("\n - ", $context)
        );
    }
}