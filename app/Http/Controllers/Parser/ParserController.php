<?php
namespace App\Http\Controllers\Parser;

use App\Http\Controllers\Controller;
use App\Models\PackageConnection;
use App\Models\TemporarySearchResults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ParserController extends Controller {
    const DEFAULT_COUNT_IN_PACKAGE = 100;

    public function getVersion() {
        return json_encode([
            "currentVersion" => TemporarySearchResults::getCurrentVersion()
        ]);
    }

    public function getPackageCount(Request $request) {
        $validation = Validator::make($request->all(),[
            'self_version' => 'required|integer|min:0',
            'elements_in_package' => 'integer|min:0',
        ]);
        if ($validation->fails()) {
            return $validation->errors()->toArray();
        }

        try {
            $arRequest = json_decode($request->getContent(), true);
            $version = $arRequest['self_version'];
            $elementsInPackage = $arRequest['elements_in_package'] ?? self::DEFAULT_COUNT_IN_PACKAGE;
            $currentVersion = TemporarySearchResults::getCurrentVersion();
            if ($version >= $currentVersion) {
                return 'Your version not need to update';
            }

            $totalResultCount = TemporarySearchResults::getCountSliceResultByVersion($version, $currentVersion);
            $packageCount = ceil($totalResultCount / $elementsInPackage);
            return json_encode([
                'package_count' => $packageCount
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'error_text' => $e->getMessage()
            ]);
        }
    }

    public function getPackageByNumber(Request $request) {
        $validation = Validator::make($request->all(),[
            'connection_key' => 'required',
            'package_number' => 'required|integer|min:0',
        ]);
        if ($validation->fails()) {
            return $validation->errors()->toArray();
        }

        try {
            $arRequest = json_decode($request->getContent(), true);
            $connection = PackageConnection::where('key', $arRequest['connection_key'])->first();
            if (!$connection) {
                return json_encode([
                    'error' => 'Connection_key does\'t exist'
                ]);
            }

            return json_encode([
                'results' => TemporarySearchResults::getPackageResults($arRequest['package_number'], $connection)
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'error_text' => $e->getMessage()
            ]);
        }
    }

    public function getConnectionId(Request $request) {
        $validation = Validator::make($request->all(),[
            'self_version' => 'required|numeric',
            'elements_in_package' => 'numeric',
        ]);
        if ($validation->fails()) {
            return $validation->errors()->toArray();
        }

        try {
            $arRequest = json_decode($request->getContent(), true);
            $version = $arRequest['self_version'];
            $elementsInPackage = $arRequest['elements_in_package'] ?? self::DEFAULT_COUNT_IN_PACKAGE;
            $currentVersion = TemporarySearchResults::getCurrentVersion();
            if ($version >= $currentVersion) {
                return 'Your version not need to update';
            }

            $totalResultCount = TemporarySearchResults::getCountSliceResultByVersion($version, $currentVersion);
            $connection = PackageConnection::createConnectionByElementsCount($version, $elementsInPackage, $totalResultCount);
            return json_encode([
                'connection_key' => $connection->key
            ]);

        } catch (\Exception $e) {
            return json_encode([
                'error_text' => $e->getMessage()
            ]);
        }
    }
}