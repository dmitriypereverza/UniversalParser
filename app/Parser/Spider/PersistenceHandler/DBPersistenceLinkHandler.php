<?php
namespace App\Parser\Spider\PersistenceHandler;

use App\Models\Links;
use VDB\Spider\PersistenceHandler\PersistenceHandlerInterface;
use VDB\Spider\Resource;

class DBPersistenceLinkHandler implements PersistenceHandlerInterface
{
    use PersistenceHandlerTrait;
    private $siteUrl;

    public function __construct($siteUrl)
    {
        $this->siteUrl = $siteUrl;
    }

    public function persist(Resource $resource)
    {
        $this->resources[] = $resource->getUri();
        $url = Links::getOrCreateLinkByUrl($resource->getUri());
        $url->title = $this->getPageTitle($resource);
        $url->save();
    }

    private function getPageTitle($resource)
    {
        $item = $resource->getCrawler()->filterXpath('//title/text()');
        if ($item->count() > 0) {
            return  trim($item->text());
        }
        return null;
    }
}
