<?php

namespace App\Parser\Spider\Attributes;

use App\Parser\CarDefiner;
use Symfony\Component\DomCrawler\Crawler;
use \VDB\Spider\Resource;

/**
 * @author d.pereverza@worksolutions.ru
 */
class TableParser implements AttributeParserInterface
{
    /**  @var array $selectors */
    private $selectors;
    /**  @var CarDefiner $carDefiner */
    private $carDefiner;

    public function __construct($selectors)
    {
        $this->selectors = $selectors;
        $this->carDefiner = new CarDefiner();
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
     * @param Crawler $resourceCrawler
     * @param $selectors
     * @return array
     */
    private function getElementSelectorsValue(Crawler $resourceCrawler, $selectors)
    {
        $result['url'] = $resourceCrawler->getUri();
        foreach ($selectors as $key => $selector) {
            if (!$content = $this->getSelectorContent($resourceCrawler, $selector['value'])) {
                unset($result);
                break;
            }
            if ($key == 'img' && array_key_exists('isRelativePath', $selector) && $selector['isRelativePath']) {
                $url = parse_url($resourceCrawler->getUri());
                $content = $url['scheme'] . '://' . $url['host'] . $content;
            }
            if ($key == 'brand') {
                $brand = $this->carDefiner->defineBrand($content);
                $brand && $content = $brand;
            }
            if ($key == 'model') {
                $model = $this->carDefiner->defineModelByBrand($result['brand'], $content);
                $model && $content = $model;
            }
            $content = $this->getFilteredContent($selector, $content);
            if ($definedContent = $this->carDefiner->defileAdditionalData($selector, $content, $result)) {
                if (array_search('', $definedContent)) {
                    unset($result);
                    break;
                }
                foreach ($definedContent as $key => $content) {
                    $result[$key] = $content;
                }
            } else {
                $result[$key] = $content;
            }
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
            $trimmedText = trim($item->text());
            return preg_replace('|\s+|', ' ', $trimmedText);
        }
    }

    /**
     * @return bool
     */
    public function isMultipleElements()
    {
        return true;
    }

    /**
     * @param $selector
     * @param $content
     * @return mixed
     */
    protected function getFilteredContent($selector, $content)
    {
        if (array_key_exists('regexp', $selector) && $selector['regexp']) {
            if (preg_match($selector['regexp'], $content, $outputArray)) {
                $content = $outputArray[0];
            }
        }
        if (array_key_exists('preg_replace', $selector) && $selector['preg_replace']) {
            $content = preg_replace($selector['preg_replace']['pattern'], $selector['preg_replace']['replace'], $content);
        }
        return $content;
    }
}