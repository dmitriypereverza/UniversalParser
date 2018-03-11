<?php
/**
 * @author d.pereverza@worksolutions.ru
 */

namespace App\Parser\Spider\PersistenceHandler;


trait PersistenceHandlerTrait
{
    /** @var Resource[] */
    private $resources = array();

    public function count()
    {
        return count($this->resources);
    }

    /**
     * @param string $spiderId
     *
     * @return void
     */
    public function setSpiderId($spiderId)
    {
    }

    /**
     * @return Resource
     */
    public function current()
    {
        return current($this->resources);
    }

    /**
     * @return Resource|false
     */
    public function next()
    {
        next($this->resources);
    }

    /**
     * @return int
     */
    public function key()
    {
        return key($this->resources);
    }

    /**
     * @return boolean
     */
    public function valid()
    {
        return (bool)current($this->resources);
    }

    /**
     * @return void
     */
    public function rewind()
    {
        reset($this->resources);
    }

}