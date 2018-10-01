<?php
namespace App\Http\Controllers\Image;

use App\Http\Controllers\Controller;
use App\Parser\Spider\RequestHandler\GuzzleRequestWIthProxyHandler;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Validator;

class ImageController extends Controller
{
    public function getImage(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'remote_url' => 'required',
        ]);
        if ($validation->fails()) {
            return response($validation->errors()->toArray(), 400);
        }
        $remouteUrl = $request->get('remote_url');
        $requestHandler = new GuzzleRequestWIthProxyHandler();

        try {
            $image = $requestHandler->requestByStringUrl($remouteUrl);
        } catch (ClientException $e) {
            return response('Error' . $e->getMessage(), 404);
        }

        return response()
            ->make($image->getBody())
            ->header("Content-Type", 'image/png');
    }
}