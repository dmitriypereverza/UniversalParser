<?php

namespace App\Parser\Spider\Attributes;

use App\Parser\CarDefiner;
use VDB\Spider\Resource;

/**
 * @author d.pereverza@worksolutions.ru
 */
class DetailPageParser implements AttributeParserInterface
{
    /**  @var array $selectors */
    private $selectors;

    public function __construct($selectors)
    {
        $this->selectors = $selectors;
    }

    public function getSelectorsValue(Resource $resource)
    {
        $result['url'] = $resource->getCrawler()->getUri();
        foreach ($this->selectors as $key => $selector) {
            $result[$key] = $this->getSelectorContent($resource, $selector['value']);
        }
        return $result;
    }

    /**
     * @param Resource $resource
     * @param $selector
     * @return string
     */
    private function getSelectorContent($resource, $selector)
    {
        $item = $resource->getCrawler()->filterXpath($selector);
        if ($item->count()) {
            $trimmedText = trim($item->text());
            return preg_replace('/\s+/', ' ', $trimmedText);
        }
    }

    /**
     * @return bool
     */
    public function isMultipleElements()
    {
        return false;
    }
}