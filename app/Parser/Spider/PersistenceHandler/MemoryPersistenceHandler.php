<?php

namespace App\Parser\Spider\PersistenceHandler;

use VDB\Spider\PersistenceHandler\PersistenceHandlerInterface;
use VDB\Spider\Resource;

class MemoryPersistenceHandler implements PersistenceHandlerInterface
{
    /**
     * @var Resource[]
     */
    private $resources = array();

    public function setIdsession($spiderId)
    {
        // memory handler ignores this. Only interesting for true persistence as some kind of key or prefix
    }

    public function count()
    {
        return count($this->resources);
    }

    public function persist(Resource $resource)
    {
        $this->resources[] = $resource;

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

    /**
     * @param string $spiderId
     *
     * @return void
     */
    public function setSpiderId($spiderId) {
        // TODO: Implement setSpiderId() method.
    }
}
