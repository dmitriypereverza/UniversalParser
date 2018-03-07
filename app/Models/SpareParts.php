<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TemporarySearchResults
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Application where($value, $value)
 * @mixin \Eloquent
 * @property \Carbon\Carbon $created_at
 * @property int $id
 * @property \Carbon\Carbon $updated_at
 * @property string $root_model_link
 * @property string $ref_model_link
 * @property string $category
 * @property string $title
 * @property string|null $article
 * @property string|null $img_url
 * @property string|null $spare_part
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SpareParts whereArticle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SpareParts whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SpareParts whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SpareParts whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SpareParts whereImgUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SpareParts whereRefModelLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SpareParts whereRootModelLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SpareParts whereSparePart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SpareParts whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SpareParts whereUpdatedAt($value)
 */
class SpareParts extends Model
{
    protected $table = 'spare_parts';

    /**
     * @param string $rootLink
     * @param string $link
     * @param $category
     * @param $adName
     * @param $article
     * @param $img
     * @return Model|void
     */
    public static function updateOrCreate($rootLink , $link, $category, $adName, $sparePart, $article, $img) {
        $spare = self::where('ref_model_link', $link)
            ->where('category', $category)
            ->where('title', $adName)
            ->get()
            ->first();

        if (!$spare) {
            $spare = new SpareParts();
            $spare->root_model_link = $rootLink;
            $spare->ref_model_link = $link;
            $spare->title = $adName;
            $spare->category = $category;
            $spare->spare_part = $sparePart;
        }
        $spare->article = $article ?? null;
        $spare->img_url = $img ?? null;
        $spare->save();
    }
}