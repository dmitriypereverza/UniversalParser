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
     * @return array
     */
    public function defileAdditionalData($selector, $content, $result)
    {
        $definedContent = [];
        if (array_key_exists('need_parse_model_and_brand', $selector) && $selector['need_parse_model_and_brand']) {
            $definedContent = $this->defineBrandAndModelByText($content);
        }
        elseif (array_key_exists('need_parse_model_by_brand', $selector) && $selector['need_parse_model_by_brand'] && $result['brand']) {
            $definedContent = [
                'model' => $this->defineModelByBrand($result['brand'], $content)
            ];
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
        $currentModel = '';
        $brand = Brand::where('name', $currentBrand)
            ->orWhere('synonym_name', $currentBrand)
            ->first();
        if (!$brand) {
            return '';
        }
        $models = Models::where('car_make_id', $brand->id)->orderBy(DB::raw('LENGTH(name), name'))->get()->reverse();
        foreach ($models as $model) {
            if (preg_match("~\b" .$model->name. "\b~i", $text)
                || preg_match("~\b" .$model->synonym_name. "\b~i", $text)) {
                $currentModel = $model->name;
                break;
            }
        }

        return $currentModel;
    }

    public function defineBrand($text)
    {
        $currentBrand = '';
        $brands = Brand::orderBy(DB::raw('LENGTH(name), name'))->get()->reverse();
        foreach ($brands as $brand) {
            if (!$brand->name) {
                continue;
            }
            if (preg_match("~\b" .$brand->name. "\b~i", $text)
                || preg_match("~\b" .$brand->synonym_name. "\b~i", $text)) {
                $currentBrand = $brand->name;
                break;
            }
        }

        return $currentBrand;
    }
}