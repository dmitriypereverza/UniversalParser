<?php

namespace App\Parser\Spider\Attributes;

use \VDB\Spider\Resource;

interface AttributeParserInterface {
    public function getSelectorsValue(Resource $resource);
    public function isMultipleElements();
}