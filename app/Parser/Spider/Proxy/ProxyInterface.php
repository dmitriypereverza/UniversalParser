<?php
/**
 * @author d.pereverza@worksolutions.ru
 */
namespace App\Parser\Spider\Proxy;

interface ProxyInterface {
    /**
     * @return string
     */
    public function getProxyUrl();

    /**
     * @return boolean
     */
    public function update();
}