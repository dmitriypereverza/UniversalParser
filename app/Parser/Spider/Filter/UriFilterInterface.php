<?php

namespace App\Parser\Spider\Filter;

/**
 * @author d.pereverza@worksolutions.ru
 */
interface UriFilterInterface
{
    /**
     * @param string $url
     * @return bool
     */
    public function match($url);
}