<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ParserStatus
 *
 * @property int $id
 * @property boolean $is_enable
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application where($value, $value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ParserStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ParserStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ParserStatus whereIsEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\ParserStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ParserStatus extends Model
{
    protected $table = 'parser_status';
    protected $guarded = [];

    /**
     * @return boolean
     */
    public static function isEnable()
    {
        return self::first()->is_enable;
    }

    public static function enable()
    {
        return self::first()->update(['is_enable' => true]);
    }

    public static function disable()
    {
        return self::first()->update(['is_enable' => false]);
    }
}