<?php
/**
 * @author d.pereverza@worksolutions.ru
 */

namespace App\Parser;

use App\Models\Brand;
use App\Models\Models;
use Illuminate\Support\Facades\DB;

class CarDefiner
{
    /**
     * @param $selector
     * @param $content
     * @param $result
     * @return array|null
     */
    public function defileAdditionalData($selector, $content, $result)
    {
        $definedContent = [];
        if (array_key_exists('need_parse_model_and_brand', $selector) && $selector['need_parse_model_and_brand']) {
            $definedContent = $this->defineBrandAndModelByText($content);
        }
        elseif (array_key_exists('need_parse_model_by_brand', $selector) && $selector['need_parse_model_by_brand'] && $result['brand']) {
            $definedContent = $this->defineModelByBrand($result['brand'], $content);
        }
        return $definedContent;
    }

    private function defineBrandAndModelByText($text)
    {
        $currentBrand = '';
        $currentModel = '';
        $brands = Brand::orderBy(DB::raw('LENGTH(name), name'))->get()->reverse();
        foreach ($brands as $brand) {
            if (!$brand->name) {
                continue;
            }
            if (stristr($text, $brand->name . ' ') || stristr($text, $brand->synonym_name . ' ')) {
                $currentBrand = $brand->name;

                $models = Models::orderBy(DB::raw('LENGTH(name), name'))->having('car_make_id', $brand->id)->get()->reverse();
                foreach ($models as $model) {
                    if (stristr($text, ' ' . $model->name . ' ') || stristr($text, ' ' . $model->synonym_name . ' ')) {
                        $currentModel = $model->name;
                    }
                }
            }
        }

        return [
            'brand' => $currentBrand,
            'model' => $currentModel
        ];
    }

    private function defineModelByBrand($currentBrand, $text)
    {
        $currentModel = '';
        $brand = Brand::where('name', $currentBrand)
            ->orWhere('synonym_name', $currentBrand)
            ->first();
        if (!$brand) {
            return null;
        }
        $models = Models::orderBy(DB::raw('LENGTH(name), name'))->having('car_make_id', $brand->id)->get()->reverse();
        foreach ($models as $model) {
            if (stristr($text, ' ' . $model->name . ' ') || stristr($text, ' ' . $model->synonym_name . ' ')) {
                $currentModel = $model->name;
            }
        }

        return [
            'model' => $currentModel
        ];
    }
}