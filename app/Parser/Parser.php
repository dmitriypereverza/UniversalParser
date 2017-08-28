<?php

namespace App\Parser;

use Symfony\Component\Yaml\Yaml;

class Parser {
    private $configName;
    private $configPath;

    function __construct() {
        $this->configName = 'parser';
        $this->configPath = '';

        if (file_exists(sprintf('%s/%s.yaml', config_path(), $this->configName))) {
            $this->configPath = sprintf('%s/%s.yaml', config_path(), $this->configName);
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