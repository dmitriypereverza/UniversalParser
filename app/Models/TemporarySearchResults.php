<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @property \Carbon\Carbon $created_at
 * @property int $id
 * @property string $config_site_name
 * @property string $id_session
 * @property string $content
 * @property string $hash
 * @property \Carbon\Carbon $updated_at
 */
class TemporarySearchResults extends Model {
    protected $table = 'tmp_search_results';

    /**
     * @param $result
     * @param $siteUrl
     * @param $sessionId
     * @return bool
     */
    public static function insertIfNotExist($result, $siteUrl, $sessionId) {
        if (!is_array($result)) {
            throw new InvalidArgumentException('Не найдены данные для записи в таблицу');
        }
        $tmpTable = new TemporarySearchResults;
        $tmpTable->config_site_name = $siteUrl;
        $tmpTable->id_session = $sessionId;
        $tmpTable->content = json_encode($result);
        $tmpTable->hash = md5(serialize($result));

        if (!self::isRowExist($tmpTable)) {
           return $tmpTable->save();
        }
    }

    /**
     * @param $tmpTable
     * @return Model|null|static
     */
    private static function isRowExist($tmpTable) {
        return self::where('hash', $tmpTable->hash)->first();
    }


    public static function setNewVersion($sessionId) {
        self::where('id_session', $sessionId)->update(['version' => self::getCurrentVersion() + 1]);
    }

    public static function getCurrentVersion() {
        return self::max('version') ?? 0;
    }

    public static function getSliceResultByVersion($versionFrom, $versionTo) {
        if ($versionTo <= $versionFrom) {
            return null;
        }
        return self::whereBetween('version', [$versionFrom, $versionTo])->get()->toJson();
    }
}
