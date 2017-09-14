<?php
namespace App\Parser\Spider\Proxy;

use App\Models\Proxy;
use Exception;
use Illuminate\Support\Facades\DB;
use SplFileObject;

class FineproxyOrgProxy implements ProxyInterface {
    public $urlProxyList;
    public $login;
    public $password;

    public function __construct() {
        $this->urlProxyList = getenv('PROXY_SERVICE_LIST_URL').'?format=txt&type=httpip&login=%s&password=%s';
        $this->login = getenv('PROXY_SERVICE_LOGIN');
        $this->password = getenv('PROXY_SERVICE_PASSWORD');
    }

    public function getProxyUrl() {
        if (!$this->login || !$this->password) {
            throw new Exception('Необходимо установить логин/пароль для доступа к прокси');
        }
        $proxy = $this->getProxy();
        return sprintf('http://%s', $proxy->url);
    }

    public function update() {
        $url = sprintf($this->urlProxyList, $this->login, $this->password);
        $stream = new SplFileObject($url);
        Proxy::where('name', $this->getId())->delete();
        while (!$stream->eof()) {
            $proxyUrl = $stream->fgets();
            $proxy = new Proxy();
            $proxy->url = trim($proxyUrl);
            $proxy->name = $this->getId();
            $proxy->save();
        }
        return true;
    }

    private function getId() {
        return sprintf('%s:%s', static::class, $this->login);
    }

    private function getProxy() {
        $proxy = Proxy::where('id', '>', DB::raw('(select `id` from `proxy` where `isLast` = 1 and `isAvailable` = 1 limit 1)'))
            ->where('isAvailable', '=', 1)
            ->first();
        if (!$proxy) {
            $proxy = Proxy::first();
        }
        if (!$proxy) {
            throw new Exception('В списках не найден адрес прокси сервера');
        }
        Proxy::where('isLast', '!=', null)->update(['isLast' => null]);
        $proxy->isLast = 1;
        $proxy->save();
        return $proxy;
    }
}