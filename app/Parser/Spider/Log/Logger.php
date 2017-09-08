<?php
namespace App\Parser\Spider\Log;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class Logger implements LoggerAwareInterface {
    use LoggerAwareTrait;

    public function __construct(LoggerInterface $logger = null) {
        $logger && $this->setLogger($logger);
    }

    private function getLogger() {
        if (!isset($this->logger)) {
            $this->setLogger(new ConsoleLogger());
        }

        return $this->logger;
    }

    public function emergency($message, array $context = array()) {
        $this->getLogger()->{__FUNCTION__}($message, $context);
    }

    public function alert($message, array $context = array()) {
        $this->getLogger()->{__FUNCTION__}($message, $context);
    }

    public function critical($message, array $context = array()) {
        $this->getLogger()->{__FUNCTION__}($message, $context);
    }

    public function error($message, array $context = array()) {
        $this->getLogger()->{__FUNCTION__}($message, $context);
    }

    public function warning($message, array $context = array()) {
        $this->getLogger()->{__FUNCTION__}($message, $context);
    }

    public function notice($message, array $context = array()) {
        $this->getLogger()->{__FUNCTION__}($message, $context);
    }

    public function info($message, array $context = array()) {
        $this->getLogger()->{__FUNCTION__}($message, $context);
    }

    public function debug($message, array $context = array()) {
        $this->getLogger()->{__FUNCTION__}($message, $context);
    }
}