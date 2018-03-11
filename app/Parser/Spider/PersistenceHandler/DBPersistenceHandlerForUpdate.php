<?php

namespace App\Parser\Spider\PersistenceHandler;

use App\Models\TemporarySearchResults;

class DBPersistenceHandlerForUpdate extends DBPersistenceHandler
{
    public function modifyAndSaveParseElement($values)
    {
        if ($modifiedVars = $this->modifiedSelectorsValue($values)) {
            $existingItem = TemporarySearchResults::where(['config_site_name' => $this->config['url']])
                ->where('version', '>', 0)
                ->whereNull('old_content')
                ->where('content->url', $modifiedVars['url'])
                ->get()
                ->toArray();
            foreach ($existingItem as $item) {
                $this->makeUpdateRecord($item, $modifiedVars);
            }
        }
    }

    private function isEqualByParameters($decodedContent, $modifiedVars, $params)
    {
        $getParamsFromArray = function ($params, $array) {
            $result = [];
            foreach ($params as $param) {
                if (isset($array[$param])) {
                    $result[$param] = $array[$param];
                }
            }
            return $result;
        };
        $storedContent = $getParamsFromArray($params, $decodedContent);
        $currentVars = $getParamsFromArray($params, $modifiedVars);
        if (md5(serialize($storedContent)) == md5(serialize($currentVars))) {
            return true;
        }
    }

    /**
     * @param $item
     * @param $modifiedVars
     */
    private function makeUpdateRecord($item, $modifiedVars)
    {
        $decodedContent = (array)json_decode($item['content']);
        if ($this->isEqualByParameters($decodedContent, $modifiedVars, ['url', 'title'])) {
            $arrayDiff = array_diff_assoc($modifiedVars, $decodedContent);
            if (!$arrayDiff) {
                return;
            }
            if ($paramKeys = array_intersect($this->config['updateParams'], array_keys($decodedContent))) {
                $isNeedUpdate = false;
                foreach ($paramKeys as $updateParam) {
                    if (isset($arrayDiff[$updateParam])) {
                        $decodedContent[$updateParam] = $arrayDiff[$updateParam];
                        $isNeedUpdate = true;
                    }
                }
                if (!$isNeedUpdate) {
                    return;
                }
                TemporarySearchResults::insertRowForUpdate(
                    $decodedContent,
                    $item['id'],
                    $this->config['url'],
                    $this->sessionId
                );
            }
        }
    }
}
