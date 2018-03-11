<?php
namespace App\Parser\Spider\Attributes;
use \VDB\Spider\Resource;

/**
 * @author d.pereverza@worksolutions.ru
 */
class DetailPageParser extends BaseAttributeParser
{
    /**
     * @param \VDB\Spider\Resource $resource
     * @return array
     */
    public function getSelectorsValue(Resource $resource)
    {
        $crawler = $resource->getCrawler();
        $result['url'] = $crawler->getUri();
        foreach ($this->selectors as $key => $selector) {
            $result[$key] = $this->getSelectorContent($crawler, $selector['value']);
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isMultipleElements()
    {
        return false;
    }
}