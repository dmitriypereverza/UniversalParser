<?php

namespace App\Parser\Spider\Attributes;

use Symfony\Component\DomCrawler\Crawler;
use \VDB\Spider\Resource;

/**
 * @author d.pereverza@worksolutions.ru
 */
class TableParser implements AttributeParserInterface
{
    /**  @var array $selectors */
    private $selectors;

    public function __construct($selectors)
    {
        $this->selectors = $selectors;
    }

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
        $item = $resource->getCrawler()->filterXpath($selector);
        $nodeList = [];
        if ($item->count()) {
            $nodeList = $item->each(function (Crawler $node, $i) {
                return $node;
            });
        }
        return $nodeList;
    }

    /**
     * @param Crawler $resourceCrawler
     * @param $selectors
     * @return array
     */
    private function getElementSelectorsValue(Crawler $resourceCrawler, $selectors)
    {
        $result['url'] = $resourceCrawler->getUri();
        foreach ($selectors as $key => $selector) {
            if (!$content = $this->getSelectorContent($resourceCrawler, $selector)) {
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
     * @param Crawler $crawler
     * @param $selector
     * @return string
     */
    private function getSelectorContent(Crawler $crawler, $selector)
    {
        $item = $crawler->filterXpath($selector);
        if ($item->count()) {
            return trim($item->text());
        }
    }

    /**
     * @return bool
     */
    public function isMultipleElements()
    {
        return true;
    }
}