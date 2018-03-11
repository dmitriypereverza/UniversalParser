<?php
namespace App\Parser\Spider\Attributes;
use Symfony\Component\DomCrawler\Crawler;
use \VDB\Spider\Resource;

/**
 * @author d.pereverza@worksolutions.ru
 */
class TableParser extends BaseAttributeParser
{
    /**
     * @param Resource $resource
     * @return array
     */
    public function getSelectorsValue(Resource $resource)
    {
        $selectors = $this->selectors;
        $rowSelector = $selectors['row'];
        unset($selectors['row']);

        $resultElements = [];
        /** @var Crawler $rowCrawler */
        foreach ($this->getRows($resource, $rowSelector) as $rowCrawler) {
            if ($element = $this->getElementSelectorsValue($rowCrawler, $selectors)) {
                $resultElements[] = $element;
            }
        }
        return $resultElements;
    }

    /**
     * @param \VDB\Spider\Resource $resource
     * @param $selector
     * @return array
     */
    private function getRows(Resource $resource, $selector)
    {
        $item = $resource->getCrawler()->filterXpath($selector['value']);
        $nodeList = [];
        if ($item->count()) {
            $nodeList = $item->each(function (Crawler $node, $i) {
                return $node;
            });
        }
        return $nodeList;
    }

    /**
     * @param Crawler $crawler
     * @param $selectors
     * @return array
     */
    private function getElementSelectorsValue(Crawler $crawler, $selectors)
    {
        $result['url'] = $crawler->getUri();
        foreach ($selectors as $key => $selector) {
            $result[$key] = $this->getSelectorContent($crawler, $selector['value']);
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isMultipleElements()
    {
        return true;
    }
}