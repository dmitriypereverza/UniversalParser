<?php

namespace App\Parser\Spider\PersistenceHandler;

use App\Models\TemporarySearchResults;
use App\Parser\CarDefiner;
use App\Parser\Spider\Attributes\AttributeParserInterface;
use App\Parser\Spider\Filter\UriFilter;
use VDB\Spider\PersistenceHandler\PersistenceHandlerInterface;
use VDB\Spider\Resource;

class DBPersistenceHandler implements PersistenceHandlerInterface
{
    /** @var Resource[] */
    private $resources = array();
    private $config;
    private $sessionId;
    /** @var $urlFilter UriFilter */
    private $urlFilter;
    /** @var $attributeParser AttributeParserInterface */
    private $attributeParser;
    private $carDefiner;

    public function __construct(AttributeParserInterface $attributeParser, $config, $sessionId, $urlFilter)
    {
        $this->config = $config;
        $this->sessionId = $sessionId;
        $this->urlFilter = $urlFilter;
        $this->attributeParser = $attributeParser;
        $this->carDefiner = new CarDefiner();
    }

    public function count()
    {
        return count($this->resources);
    }

    public function persist(Resource $resource)
    {
        echo $resource->getUri() . "\n";
        if (!$this->urlFilter->match($resource->getUri())) {
            return;
        }
        $this->resources[] = $resource->getUri();
        $selectorVals = $this->attributeParser->getSelectorsValue($resource);
        if (!$this->attributeParser->isMultipleElements()) {
            $this->modifyAndSaveParseElement($selectorVals);
        }
        else {
            foreach ($selectorVals as $rowValues) {
                $this->modifyAndSaveParseElement($rowValues);
            }
        }
    }

    private function modifyAndSaveParseElement($values)
    {
        if ($modifiedVars = $this->modifiedSelectorsValue($values)) {
            TemporarySearchResults::insertIfNotExist($modifiedVars, $this->config['url'], $this->sessionId);
        }
    }

    private function modifiedSelectorsValue($selectorVals)
    {
        $result = [];
        $result['url'] = $selectorVals['url'];
        $selectors = $this->config['selectors'];
        unset($selectors['row']);
        foreach ($selectors as $key => $selector) {
            $content = $selectorVals[$key];
            $content = $this->getFilteredContent($selector, $content);
            if (!$content) {
                if (!array_key_exists('optional', $selector) || !$selector['optional']) {
                    unset($result);
                    break;
                }
            }
            if ($key == 'img' && $this->getSelectorParam('isRelativePath', $selector)) {
                $url = parse_url($selectorVals['url']);
                $content = $url['scheme'] . '://' . $url['host'] . $content;
            }
            if ($key == 'brand') {
                $brand = $this->carDefiner->defineBrand($content);
                $brand && $content = $brand;
            }
            if ($key == 'model') {
                $model = $this->carDefiner->defineModelByBrand($selectorVals['brand'], $content);
                $model && $content = $model;
            }
            if ($definedContent = $this->carDefiner->defileAdditionalData($selector, $content, $selectorVals)) {
                if (in_array('', $definedContent)) {
                    unset($result);
                    break;
                }
                $result = array_merge($result, $definedContent);
            }
            else {
                $result[$key] = $content;
            }


            $combineField = $this->combineFields($result, $selector);
            $combineField && $result[$key] = $combineField;
        }

        return $result ?? null;
    }

    /**
     * @param $selector
     * @param $content
     * @return mixed
     */
    protected function getFilteredContent($selector, $content)
    {
        $regexp = $this->getSelectorParam('regexp', $selector);
        if ($regexp) {
            if (preg_match($regexp, $content, $outputArray)) {
                $content = $outputArray[0];
            } else {
                $content = '';
            }
        }

        $replace = $this->getSelectorParam('preg_replace', $selector);
        if ($replace) {
            $content = preg_replace($selector['preg_replace']['pattern'], $selector['preg_replace']['replace'], $content);
        }

        return $content;
    }

    /**
     * @param $resultVals
     * @param $selector
     * @return string
     */
    private function combineFields($resultVals, $selector)
    {
        $result = '';
        /** @var array $composition */
        $composition = $this->getSelectorParam('composition', $selector);
        if ($composition) {
            $resParams = [];
            foreach ($composition['params'] as $param) {
                $resParams[] = $resultVals[$param];
            }
            $result = vsprintf($selector['composition']['pattern'], $resParams);
        }
        return $result;
    }

    private function getSelectorParam($string, $selector)
    {
        return array_key_exists($string, $selector) ? $selector[$string] : '';
    }

    /**
     * @param string $spiderId
     *
     * @return void
     */
    public function setSpiderId($spiderId)
    {
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
}
