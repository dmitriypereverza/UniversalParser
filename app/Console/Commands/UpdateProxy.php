<?php
namespace App\Console\Commands;

use App\Parser\Spider\Proxy\FineproxyOrgProxy;
use Illuminate\Console\Command;

class UpdateProxy extends Command {
    protected $signature = 'proxy:update';

    protected $description = 'Update proxy list';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $proxy = new FineproxyOrgProxy();
        $proxy->update();
    }
}