<?php

namespace App\Parser\Spider\Log;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Psr\Log\AbstractLogger;

class ConsoleLogger extends AbstractLogger
{
    public function log($level, $message, array $context = [])
    {
        echo $this->getFormatedText($level, $message, $context);
    }

    /**
     * @param $level
     * @param $message
     * @param array $context
     * @return string
     */
    private function getFormatedText($level, $message, array $context = [])
    {
        $text = sprintf("\n[%s] %s: %s;", Carbon::now(Config::get('app.timezone')), strtoupper($level), $message);
        if ($context) {
            $text .= sprintf('Context: [%s]', json_encode($context));
        }
        return $text;
    }
}