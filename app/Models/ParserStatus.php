<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property boolean $is_enable
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ParserStatus extends Model {
    protected $table = 'parser_status';
    protected $guarded = [];
    /**
     * @return boolean
     */
    public static function isEnable() {
        return self::first()->is_enable;
    }

    public static function enable() {
        return self::first()->update(['is_enable' => true]);
    }

    public static function disable() {
        return self::first()->update(['is_enable' => false]);
    }
}