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

    function __construct()
    {
        foreach ($this->configTypes as $configType) {
            if (!file_exists($this->getPathByType($configType))) {
                throw new InvalidArgumentException('File ' . $configType . '.yaml does\'t exist');
            }
        }
    }

    public function getArrayConfigByType($typeConfig) {
        if (!in_array($typeConfig, $this->configTypes)) {
            throw new InvalidArgumentException('Type ' . $typeConfig . 'does\'t exist.');
        }
        return Yaml::parse(file_get_contents($this->getPathByType($typeConfig)));
    }

    public function getArrayConfig()
    {
        foreach ($this->configTypes as $configType) {
            if (!file_exists($this->getPathByType($configType))) {
                throw new InvalidArgumentException('File ' . $configType . '.yaml does\'t exist');
            }
        }
        $mainConfig = Yaml::parse(file_get_contents($this->getPathByType(self::SITE_CONFIG)));
        $config = $this->addSheduleField($mainConfig);

        return $config;
    }

    public function getError()
    {
        try {
            $this->getArrayConfigByType(self::SCHEDULE_CONFIG);
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

    /**
     * @param $configType
     * @return string
     */
    private function getPathByType($configType)
    {
        return sprintf('%s/%s.yaml', config_path(), $configType);
    }

    private function addSheduleField($mainConfig)
    {
        $scheduleConfig = Yaml::parse(file_get_contents($this->getPathByType(self::SCHEDULE_CONFIG)));
        foreach ($mainConfig as $siteName => &$site) {
            $site['work_time'] = $scheduleConfig[$siteName];
        }
        return $mainConfig;
    }
}