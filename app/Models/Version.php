<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Proxy
 *
 * @property int $id
 * @property int $element_count
 * @property int $version
 * @property \Carbon\Carbon $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application where($value, $value)
 * @mixin \Eloquent
 */
class Version extends Model
{
    protected $table = 'versions';

    public static function getNextEmptyVersion()
    {
        return self::getCurrentVersion() + 1;
    }

    public static function getCurrentVersion()
    {
        return self::max('version') ?? 0;
    }
}