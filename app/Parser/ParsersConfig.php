<?php

namespace App\Parser;

use Psr\Log\InvalidArgumentException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class ParsersConfig
{
    const SITE_CONFIG = 'parser';
    const SCHEDULE_CONFIG = 'schedule';
    private $configTypes = [
        self::SITE_CONFIG,
        self::SCHEDULE_CONFIG
    ];

    private $configPath = '';

    function __construct($configType)
    {
        if (!$configType) {
            $configType = self::SITE_CONFIG;
        }
        if (!in_array($configType, $this->configTypes)) {
            throw new InvalidArgumentException('Type ' . $configType . 'does\'t exist.');
        }
        if (!file_exists(sprintf('%s/%s.yaml', config_path(), $configType))) {
            throw new InvalidArgumentException('File ' . $configType . '.yaml does\'t exist');
        }
        $this->configPath = sprintf('%s/%s.yaml', config_path(), $configType);
    }

    public function getArrayConfig()
    {
        return Yaml::parse(file_get_contents($this->configPath));
    }

    public function getTextConfig()
    {
        if (file_exists($this->configPath)) {
            return file_get_contents($this->configPath);
        }
    }

    public function getError()
    {
        try {
            $this->getArrayConfig();
        } catch (ParseException $e) {
            return $e->getMessage();
        }
    }

    public function getSiteConfig($siteName)
    {
        $arrayConfig = self::getArrayConfig();
        if (!isset($arrayConfig[$siteName])) {
            throw new InvalidArgumentException(sprintf('Site %s doesn\'t exist in %s.yaml', $siteName, self::SITE_CONFIG));
        }
        return $arrayConfig[$siteName];
    }
}