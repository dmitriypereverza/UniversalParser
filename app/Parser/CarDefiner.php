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
        $currentBrand = $this->defineBrand($text);
        $currentBrand && $currentModel = $this->defineModelByBrand($currentBrand, $text);
        return [
            'brand' => $currentBrand ?? '',
            'model' => $currentModel ?? ''
        ];
    }

    public function defineModelByBrand($currentBrand, $text)
    {
        $brand = Brand::where('name', $currentBrand)
            ->orWhere('synonym_name', $currentBrand)
            ->first();
        if (!$brand) {
            return null;
        }
        $models = Models::where('car_make_id', $brand->id)->orderBy(DB::raw('LENGTH(name), name'))->get()->reverse();
        foreach ($models as $model) {
            if (preg_match("~\b" .$model->name. "\b~i", $text)
                || preg_match("~\b" .$model->synonym_name. "\b~i", $text)) {
                return $model->name;
            }
        }
    }

    public function defineBrand($text)
    {
        $brands = Brand::orderBy(DB::raw('LENGTH(name), name'))->get()->reverse();
        foreach ($brands as $brand) {
            if (!$brand->name) {
                continue;
            }
            if (preg_match("~\b" .$brand->name. "\b~i", $text)
                || preg_match("~\b" .$brand->synonym_name. "\b~i", $text)) {
                return  $brand->name;
            }
        }
    }
}