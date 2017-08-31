<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class TemporarySearchResults extends Model {
    protected $table = 'tmp_search_results';

    public static function setVersion($version, $sessionId) {
        return self::where('id_session', $sessionId)->update(['version' => $version]);
    }


    public static function insertToTempTable($result, $siteUrl, $sessionId) {
        if (!is_array($result)) {
            throw new InvalidArgumentException('Передан неверный аргумент при записи во временную таблицу');
        }
        $tmpTable = new TemporarySearchResults;
        $tmpTable->config_site_name = $siteUrl;
        $tmpTable->id_session = $sessionId;
        $tmpTable->content = json_encode($result);
        $tmpTable->hash = md5(serialize($result));

        $raw = self::where('hash', $tmpTable->hash)->first();
        return !$raw && $tmpTable->save();
    }
}
