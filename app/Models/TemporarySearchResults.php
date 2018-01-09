<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * App\Models\TemporarySearchResults
 *
 * @property \Carbon\Carbon $created_at
 * @property int $id
 * @property string $config_site_name
 * @property string $id_session
 * @property string $content
 * @property string $hash
 * @property \Carbon\Carbon $updated_at
 * @property int|null $version
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application where($value, $value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TemporarySearchResults whereConfigSiteName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TemporarySearchResults whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TemporarySearchResults whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TemporarySearchResults whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TemporarySearchResults whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TemporarySearchResults whereIdSession($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TemporarySearchResults whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TemporarySearchResults whereVersion($value)
 * @mixin \Eloquent
 */
class TemporarySearchResults extends Model
{
    protected $table = 'tmp_search_results';

    /**
     * @param $result
     * @param $siteUrl
     * @param $sessionId
     * @return bool
     */
    public static function insertIfNotExist($result, $siteUrl, $sessionId)
    {
        if (!is_array($result)) {
            throw new InvalidArgumentException('Не найдены данные для записи в таблицу');
        }
        $tmpTable = new TemporarySearchResults;
        $tmpTable->config_site_name = $siteUrl;
        $tmpTable->id_session = $sessionId;
        $tmpTable->content = json_encode($result);

        unset($result['url']);
        $tmpTable->hash = md5(serialize($result));

        if (!self::isRowExist($tmpTable)) {
            return $tmpTable->save();
        }
    }

    public static function deleteSessionResult($sessionId)
    {
        self::whereIdSession($sessionId)->delete();
    }

    /**
     * @param $tmpTable
     * @return Model|null|static
     */
    private static function isRowExist($tmpTable)
    {
        return self::whereHash($tmpTable->hash)->first();
    }

    public static function setVersion($sessionId, $version)
    {
        self::whereIdSession($sessionId)->update(['version' => $version]);
    }

    public static function getCountElementsInSession($sessionId)
    {
        return self::whereIdSession($sessionId)->count();
    }

    public static function getCurrentVersion()
    {
        return self::max('version') ?? 0;
    }

    public static function getCountResultByVersion($versionTo)
    {
        return self::where('version', $versionTo + 1)->count();
    }

    /**
     * @param $packageNumber
     * @param PackageConnection $connection
     * @return int|null
     */
    public static function getPackageResults($packageNumber, $connection)
    {
        $offset = ($packageNumber - 1) * $connection->elements_in_package;
        $countResult = $connection->elements_in_package;
        if ($offset + $connection->elements_in_package > $connection->elements_count) {
            $countResult = $connection->elements_count % $connection->elements_in_package;
        }
        return self::where('version',$connection->version_from + 1)
            ->offset($offset)
            ->limit($countResult)
            ->get()
            ->toJson();
    }
}
