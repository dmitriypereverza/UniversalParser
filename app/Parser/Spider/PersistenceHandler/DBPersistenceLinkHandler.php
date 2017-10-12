<?php

namespace App\Parser\Spider\PersistenceHandler;

use App\Models\Links;
use App\Models\TemporarySearchResults;
use App\Parser\Spider\Attributes\AttributeParserInterface;
use App\Parser\Spider\Filter\UriFilter;
use VDB\Spider\PersistenceHandler\PersistenceHandlerInterface;
use VDB\Spider\Resource;

class DBPersistenceLinkHandler implements PersistenceHandlerInterface
{
    private $siteUrl;
    private $links = [];

    public function __construct($siteUrl)
    {
        $this->siteUrl = $siteUrl;

    }

    public function count()
    {
        return count($this->links);
    }

    public function persist(Resource $resource)
    {
        echo $resource->getUri() . "\n";
        $this->links[] = $resource->getUri();

        $url = Links::getOrCreateLinkByUrl($resource->getUri());
        $url->title = $this->getPageTitle($resource);
        $url->save();
    }

    /**
     * @return Resource
     */
    public function current()
    {
        return current($this->links);
    }

    /**
     * @return Resource|false
     */
    public function next()
    {
        next($this->links);
    }

    /**
     * @return int
     */
    public function key()
    {
        return key($this->links);
    }

    /**
     * @return boolean
     */
    public function valid()
    {
        return (bool)current($this->links);
    }

    /**
     * @return void
     */
    public function rewind()
    {
        reset($this->links);
    }

    /**
     * @param string $spiderId
     *
     * @return void
     */
    public function setSpiderId($spiderId)
    {
    }

    private function getPageTitle($resource)
    {
        $item = $resource->getCrawler()->filterXpath('//title/text()');
        if ($item->count()) {
            return  trim($item->text());
        }
        return null;
    }
}
