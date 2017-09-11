<?php
namespace App\Http\Controllers\Parser;

use App\Http\Controllers\Controller;
use App\Models\TemporarySearchResults;
use Illuminate\Http\Request;

class ParserController extends Controller {
    public function getVersion() {
        return json_encode([
            'currentVersion' => TemporarySearchResults::getCurrentVersion()
        ]);
    }

    public function getResource(Request $request) {
        try {
            $arRequest = json_decode($request->getContent(), true);
            $version = $arRequest['self_version'];
            $currentVersion = TemporarySearchResults::getCurrentVersion();

            if ($version >= $currentVersion) {
                return 'Your version not need to update';
            }

            return TemporarySearchResults::getSliceResultByVersion($version, $currentVersion);

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}