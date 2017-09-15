<?php

namespace App\Parser\Spider\Log;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;

class FileLogger extends AbstractLogger
{
    private $logDir;

    public function __construct($dirPath)
    {
        if (!$dirPath) {
            throw new InvalidArgumentException('Don\'t pass log dir!');
        }
        $this->logDir = $dirPath;
    }

    public function log($level, $message, array $context = [])
    {
        $logFile = $this->getLogFile();
        $flags = null;
        if (file_exists($logFile)) {
            $flags = FILE_APPEND;
        }
        file_put_contents($logFile, $this->getFormatedText($level, $message, $context), $flags);
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

    /**
     * @return string
     */
    public function getLogDir()
    {
        if (!is_dir(storage_path() . $this->logDir)) {
            mkdir(storage_path() . $this->logDir, 0777, true);
        }
        return storage_path() . $this->logDir;
    }

    private function getLogFile()
    {
        $fileName = Carbon::now(Config::get('app.timezone'))->format('Y-m-d');
        return $this->getLogDir() . '/' . $fileName;
    }
}