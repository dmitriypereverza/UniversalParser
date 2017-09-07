<?php

namespace App\Parser;

use Symfony\Component\Yaml\Yaml;

class Parser {
    const CONFIG_NAME = 'parser';
    private $configPath = '';

    function __construct() {
        if (file_exists(sprintf('%s/%s.yaml', config_path(), self::CONFIG_NAME))) {
            $this->configPath = sprintf('%s/%s.yaml', config_path(), self::CONFIG_NAME);
        }
    }

    public function getArrayConfig() {
        return Yaml::parse(file_get_contents($this->configPath));
    }

    public function getTextConfig() {
        if (file_exists($this->configPath)) {
            return file_get_contents($this->configPath);
        }
    }
}