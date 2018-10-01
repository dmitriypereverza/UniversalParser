<?php

namespace App\Parser\Spider\Filter;

class UriFilter implements UriFilterInterface
{
    /**
     * @var array An array of regexes
     */
    public $regexes = array();

    public function __construct(array $regexes = array())
    {
        $this->regexes = $regexes;
    }

    public function match($uri)
    {
        foreach ($this->regexes as $regex) {
            if (preg_match($regex, $uri->toString())) {
                return true;
            }
        }
        return false;
    }
}
