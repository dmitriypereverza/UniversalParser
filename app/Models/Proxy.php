<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $url
 * @property string $name
 * @property int $isLast
 * @property int $isAvailable
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Proxy extends Model {
    protected $table = 'proxy';
}