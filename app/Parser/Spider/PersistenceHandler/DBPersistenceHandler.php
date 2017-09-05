<?php

namespace App\Parser\Spider\PersistenceHandler;

use App\Models\TemporarySearchResults;
use App\Parser\Spider\Filter\UriFilter;
use InvalidArgumentException;
use VDB\Spider\Filter\PreFetchFilterInterface;
use VDB\Spider\PersistenceHandler\PersistenceHandlerInterface;
use VDB\Spider\Resource;


class DBPersistenceHandler implements PersistenceHandlerInterface
{
    /**
     * @var Resource[]
     */
    private $resources = array();

    private $selectors;
    private $siteUrl;
    private $sessionId;
    /**
     * @var $urlFilter UriFilter
     */
    private $urlFilter;

    public function __construct($selectors, $siteUrl, $sessionId, $urlFilter) {
        $this->selectors = $selectors;
        $this->siteUrl = $siteUrl;
        $this->sessionId = $sessionId;
        $this->urlFilter = $urlFilter;
    }

    public function count()
    {
        return count($this->resources);
    }

    public function persist(Resource $resource)
    {
        if(!$this->urlFilter->match($resource->getUri())) {
            return;
        }

        if ($selectorVals = $this->getSelectorValues($resource)) {
            $insertSuccess = TemporarySearchResults::insertToTempTable($selectorVals, $this->siteUrl, $this->sessionId);
            $insertSuccess && $this->resources[] = $resource;
        }
    }

    private function getSelectorValues($resource) {
        $result['url'] = $resource->getCrawler()->getUri();
        echo $result['url'] . "\n";
        foreach ($this->selectors as $key => $selector) {
            if (!$content = $this->getSelectorContent($resource, $selector)) {
                unset($result);
                continue;
            }
            $result[$key] = $content;
        }

        if (isset($result)) {
            return $result;
        }
    }

    /**
     * @param Resource $resource
     * @param $selector
     * @return mixed
     */
    private function getSelectorContent($resource, $selector) {
        $item = $resource->getCrawler()->filterXpath($selector);
        if ($item->count()) {
            return trim($item->html());
        }
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
    public function setIdsession($spiderId) {

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
