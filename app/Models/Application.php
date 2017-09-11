<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model {
    protected $table = 'applications';

    public static function whereKeyAndSecret($appKey, $appSecret) {
        return self::where('key', '=', $appKey)
            ->where('secret', '=', $appSecret);
    }
}
