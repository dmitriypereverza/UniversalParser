<?php

namespace App\Http\Controllers\Parser;

use App\Http\Controllers\Controller;
use App\Models\PackageConnection;
use App\Models\TemporarySearchResults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ParserController extends Controller
{
    const DEFAULT_COUNT_IN_PACKAGE = 100;

    public function getVersion()
    {
        return json_encode([
            "currentVersion" => TemporarySearchResults::getCurrentVersion()
        ]);
    }

    public function getPackageCount(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'self_version' => 'required|integer|min:0',
            'elements_in_package' => 'integer|min:0',
        ]);
        if ($validation->fails()) {
            return response($validation->errors()->toArray(), 400);
        }

        try {
            $arRequest = json_decode($request->getContent(), true);
            $version = $arRequest['self_version'];
            $elementsInPackage = $arRequest['elements_in_package'] ?? self::DEFAULT_COUNT_IN_PACKAGE;
            $currentVersion = TemporarySearchResults::getCurrentVersion();
            if ($version >= $currentVersion) {
                return response('Your version not need to update', 400);
            }

            $totalResultCount = TemporarySearchResults::getCountSliceResultByVersion($version, $currentVersion);
            $packageCount = ceil($totalResultCount / $elementsInPackage);
            return json_encode([
                'package_count' => $packageCount
            ]);
        } catch (\Exception $e) {
            return response(json_encode([
                'error' => $e->getMessage()
            ]), 400);
        }
    }

    public function getConnectionInfo(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'connection_key' => 'required',
        ]);
        if ($validation->fails()) {
            return response($validation->errors()->toArray(), 400);
        }
        $arRequest = json_decode($request->getContent(), true);
        $connection = PackageConnection::where('key', $arRequest['connection_key'])->first();
        if (!$connection) {
            return response('Connection_key does\'t exist', 404);
        }

        return json_encode([
            'version_from' => $connection->version_from,
            'elements_count' => $connection->elements_count,
            'elements_in_package' => $connection->elements_in_package,
            'created_at' => $connection->created_at,
        ]);
    }

    public function getPackageByNumber(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'connection_key' => 'required',
            'package_number' => 'required|integer|min:0',
        ]);
        if ($validation->fails()) {
            return response($validation->errors()->toArray(), 400);
        }
        try {
            $arRequest = json_decode($request->getContent(), true);
            $connection = PackageConnection::where('key', $arRequest['connection_key'])->first();
            if (!$connection) {
                return response('Connection_key does\'t exist', 404);
            }
            $packageResults = TemporarySearchResults::getPackageResults($arRequest['package_number'], $connection);
            return response()->json([
                'results' => $packageResults
            ]);
        } catch (\Exception $e) {
            return response(json_encode([
                'error' => $e->getMessage()
            ]), 400);
        }
    }

    public function getConnectionId(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'self_version' => 'required|numeric',
            'elements_in_package' => 'numeric',
        ]);
        if ($validation->fails()) {
            return response($validation->errors()->toArray(), 400);
        }
        try {
            $arRequest = json_decode($request->getContent(), true);
            $version = $arRequest['self_version'];
            $elementsInPackage = $arRequest['elements_in_package'] ?? self::DEFAULT_COUNT_IN_PACKAGE;
            $currentVersion = TemporarySearchResults::getCurrentVersion();
            if ($version >= $currentVersion) {
                return response('Your version not need to update', 400);
            }
            $totalResultCount = TemporarySearchResults::getCountSliceResultByVersion($version, $currentVersion);
            $connection = PackageConnection::createConnectionByElementsCount($version, $elementsInPackage, $totalResultCount);
            return json_encode([
                'connection_key' => $connection->key
            ]);

        } catch (\Exception $e) {
            return response(json_encode([
                'error' => $e->getMessage()
            ]), 400);
        }
    }
}