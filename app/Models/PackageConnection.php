<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * App\Models\PackageConnection
 *
 * @property \Carbon\Carbon $created_at
 * @property int $id
 * @property string $key
 * @property integer $elements_in_package
 * @property integer $version_from
 * @property integer $elements_count
 * @property \Carbon\Carbon $updated_at
 * @property mixed $table
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PackageConnection extends Model {
    protected $table = 'package_connection';

    public static function createConnectionByElementsCount($versionFrom, $countElementsInPackage, $elements_count) {
        $connection = new self();
        $connection->key = md5($countElementsInPackage . microtime(true));
        $connection->elements_in_package = $countElementsInPackage;
        $connection->version_from = $versionFrom;
        $connection->elements_count = $elements_count;
        $connection->save();

        return $connection;
    }
}