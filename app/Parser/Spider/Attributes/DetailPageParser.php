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
    /**  @var CarDefiner $carDefiner */
    private $carDefiner;

    public function __construct($selectors)
    {
        $this->selectors = $selectors;
        $this->carDefiner = new CarDefiner();
    }

    public function getSelectorsValue(Resource $resource)
    {
        $result['url'] = $resource->getCrawler()->getUri();
        foreach ($this->selectors as $key => $selector) {
            if (!$content = $this->getSelectorContent($resource, $selector['value'])) {
                $content = $this->getFilteredContent($selector, $content);
                if (!array_key_exists('optional', $selector) || !$selector['optional']) {
                    unset($result);
                    break;
                }
            }
            if ($key == 'img' && array_key_exists('isRelativePath', $selector) && $selector['isRelativePath']) {
                $url = parse_url($resource->getCrawler()->getUri());
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
            if ($definedContent = $this->carDefiner->defileAdditionalData($selector, $content, $result)) {
                if (in_array('', $definedContent)) {
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